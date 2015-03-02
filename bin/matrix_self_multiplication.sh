#!/bin/bash

cd $(dirname $0)

source "./sh_loader.sh"


#lt < http://www.cnblogs.com/myitm/archive/2012/07/05/2577416.html
if [ $# -eq 2 ] ; then
    path_input=$1
    path_output=$2
else
    echo "USAGE: Matrix AXB * BXA = AXA 
    echo "       \$1 input  e.g.:index_a_1    {\"index_b_1\":\"0.4115\",\"index_b_2\":\"0.3351\"}"
    echo "       \$2 output e.g.:index_a_1    {\"index_a_1\":\"1.0000\",\"index_a_2\":\"0.3351\"}"
    exit 1
fi


jobName="maxtrix_self_similarity_compute_job"
jobDisplayPrefixName="${ENV_JOB_MANAGE_USER}_${ENV_APP_NAME}_${jobName}"

jobDate=$(date --date -0hour "+%Y%m%d%H%M%S")
jobTmpDataDir="${ENV_HADOOP_TMP_DIR}/${ENV_APP_STAGE}/${jobName}/${jobDate}"

echo "#---------------------------------------------------------------#"
echo "INFO: self_similarity_compute job begin, tmp dir:${jobTmpDataDir}"
echo "INFO: job input_path:${path_input}"
echo "INFO: job input_path:${path_output}"
echo "#---------------------------------------------------------------#"
path_s0_src="${jobTmpDataDir}/s0_src"
path_s1_normalize="${jobTmpDataDir}/s1_normalize"
path_s2_transpose="${jobTmpDataDir}/s2_transpose"
path_s3_normalize="${jobTmpDataDir}/s3_normalize"
path_s4_pre_split="${jobTmpDataDir}/s4_pre_split"
path_s5_generate_dic="${jobTmpDataDir}/s5_generate_dic"
path_s6_multiplication="${jobTmpDataDir}/s6_multiplication"
path_s7_post_merge="${jobTmpDataDir}/s7_post_merge"
path_s8_rs_vectorize="${jobTmpDataDir}/s8_rs_vectorize"

echo "begin step0 inital compute resources"
cleanHDFSFolder $jobTmpDataDir
hadoop fs -mkdir $jobTmpDataDir
hadoop fs -mkdir $path_s0_src
hadoop fs -cp $path_input"/*" $path_s0_src
  
  
cleanHDFSFolder $path_s1_normalize    
step1JobName="${jobDisplayPrefixName}_s1_normalize"
hadoop streaming \
    -input $path_s0_src \
    -output $path_s1_normalize \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixRowNormalizeMapper.php 2 2 100 " \
    -reducer "cat" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=10  \
    -jobconf mapred.reduce.tasks=10  \
    -jobconf mapred.job.name="${step1JobName}"
checkJobStatus $step1JobName $?    
   
cleanHDFSFolder $path_s2_transpose     
step2JobName="${jobDisplayPrefixName}_s2_transpose"
hadoop streaming \
    -input $path_s1_normalize \
    -output $path_s2_transpose \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixTransposeMapper.php -env mapreduce" \
    -reducer "${ENV_HADOOP_PHP_PATH} ./PmatrixTransposeReducer.php -env mapreduce" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=10  \
    -jobconf mapred.reduce.tasks=10  \
    -jobconf mapred.job.name="${step2JobName}"
checkJobStatus $step2JobName $?    
   
cleanHDFSFolder $path_s3_normalize
step3JobName="${jobDisplayPrefixName}_s3_normalize"
hadoop streaming \
    -input $path_s2_transpose \
    -output $path_s3_normalize \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixRowNormalizeMapper.php 2 2 -1" \
    -reducer "cat" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=10  \
    -jobconf mapred.reduce.tasks=10  \
    -jobconf mapred.job.name="${step3JobName}" 
checkJobStatus $step3JobName $? 

cleanHDFSFolder $path_s4_pre_split  
step4JobName="${jobDisplayPrefixName}_s4_pre_split"
hadoop streaming \
    -input $path_s3_normalize \
    -output $path_s4_pre_split \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixMultiplicationPreSplitMapper.php 100" \
    -reducer "cat" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=10  \
    -jobconf mapred.reduce.tasks=10  \
    -jobconf mapred.job.name="${step4JobName}" 
checkJobStatus $step4JobName $? 
  
cleanHDFSFolder $path_s5_generate_dic  
step5JobName="${jobDisplayPrefixName}_s5_generate_dic"
hadoop streaming \
    -input $path_s4_pre_split \
    -output $path_s5_generate_dic \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixRowDicMapper.php -env mapreduce" \
    -reducer "${ENV_HADOOP_PHP_PATH} ./PmatrixRowDicReducer.php -env mapreduce" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=10  \
    -jobconf mapred.reduce.tasks=1  \
    -jobconf mapred.output.key.comparator.class=org.apache.hadoop.mapred.lib.KeyFieldBasedComparator   \
    -jobconf mapred.text.key.comparator.options=-nr  \
    -jobconf mapred.job.name="${step5JobName}"
checkJobStatus $step5JobName $? 
  
cleanHDFSFolder $path_s6_multiplication  
step6JobName="${jobDisplayPrefixName}_s6_multiplication"
hadoop streaming \
    -cacheFile "hdfs://namenode.safe.lycc.qihoo.net:9000${path_s5_generate_dic}#key_dic" \
    -input $path_s4_pre_split \
    -output $path_s6_multiplication \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixSelfMultiplicationMapper.php key_dic" \
    -reducer "${ENV_HADOOP_PHP_PATH} ./PmatrixSelfMultiplicationReducer.php -env mapreduce" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=20000  \
    -jobconf mapred.reduce.tasks=1000  \
    -jobconf mapred.job.name="${step6JobName}"
checkJobStatus $step6JobName $?      
    
cleanHDFSFolder $path_s7_post_merge  
step7JobName="${jobDisplayPrefixName}_s7_post_merge"
hadoop streaming \
    -input $path_s6_multiplication \
    -output $path_s7_post_merge \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixMultiplicationPostMergeMapper.php -env mapreduce" \
    -reducer "${ENV_HADOOP_PHP_PATH} ./PmatrixMultiplicationPostMergeReducer.php -env mapreduce" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=200  \
    -jobconf mapred.reduce.tasks=200  \
    -jobconf mapred.job.name="${step7JobName}"
checkJobStatus $step7JobName $? 
   
cleanHDFSFolder $path_s8_rs_vectorize  
step8JobName="${jobDisplayPrefixName}_s8_rs_vectorize "
hadoop streaming \
    -input $path_s7_post_merge \
    -output $path_s8_rs_vectorize \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixRowVectorizeMapper.php -env mapreduce" \
    -reducer "${ENV_HADOOP_PHP_PATH} ./PmatrixRowVectorizeReducer.php -env mapreduce" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=10  \
    -jobconf mapred.reduce.tasks=1  \
    -jobconf mapred.job.name="${step8JobName}"
checkJobStatus $step8JobName $?  
   
hadoop fs -mkdir $path_output
hadoop fs -cp $path_s8_rs_vectorize"/*" $path_output
echo "finished ${jobDisplayPrefixName}, output:${path_output}"
