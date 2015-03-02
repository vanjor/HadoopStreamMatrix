#!/bin/bash

cd $(dirname $0)

source "./sh_loader.sh"

#lt < http://www.cnblogs.com/myitm/archive/2012/07/05/2577416.html
if [ $# -eq 3 ] ; then
    path_input_a=$1
    path_input_b=$2
    path_output=$3
else
    echo "USAGE: Matrix AXB * BXC = AXC 
    echo "       \$1 input_matrix_a,line e.g.:index_a_1    {\"index_b_1\":\"0.4115\",\"index_b_2\":\"0.3351\"}"
    echo "       \$2 input_matrix_b,line e.g.:index_b_1    {\"index_c_1\":\"0.4115\",\"index_c_2\":\"0.3351\"}"
    echo "       \$2 output, should empty folder, result line.e.g.:index_a_1    {\"index_c_1\":\"0.1231\",\"index_c_2\":\"0.6762\"}"
    exit 1
fi

jobName="maxtrix_similarity_compute_job"
jobDisplayPrefixName="${ENV_JOB_MANAGE_USER}_${ENV_APP_NAME}_${jobName}"
# matrix compute reference:http://importantfish.com/two-step-matrix-multiplication-with-hadoop/

# result as jobDate=20141111120110 ,generate matrix compute tmp dir
jobDate=$(date --date -0hour "+%Y%m%d%H%M%S")
jobTmpDataDir="${ENV_HADOOP_TMP_DIR}/${ENV_APP_STAGE}/${jobName}/${jobDate}"

echo "#---------------------------------------------------------------#"
echo "INFO: similarity_compute job begin, tmp dir:${jobTmpDataDir}"
echo "INFO: job input_path_a:${path_input_a}"
echo "INFO: job input_path_b:${path_input_b}"
echo "INFO: job input_path:${path_output}"
echo "#---------------------------------------------------------------#"
path_s0_src_a="${jobTmpDataDir}/s0_src_a"
path_s0_src_b="${jobTmpDataDir}/s0_src_b"
path_s1_pre_split_a="${jobTmpDataDir}/s1_pre_split_a"
path_s2_pre_split_b="${jobTmpDataDir}/s2_pre_split_b"
path_s3_generate_dic_a="${jobTmpDataDir}/s3_generate_dic_a"
path_s4_generate_dic_b="${jobTmpDataDir}/s4_generate_dic_b"
path_s5_pre_multiplication_a="${jobTmpDataDir}/s5_pre_multiplication_a"
path_s6_pre_multiplication_b="${jobTmpDataDir}/s6_pre_multiplication_b"
path_s7_multiplication="${jobTmpDataDir}/s7_multiplication"
path_s8_post_merge="${jobTmpDataDir}/s8_post_merge"
path_s9_rs_vectorize="${jobTmpDataDir}/s9_rs_vectorize"

echo "begin step0 inital compute resources"
cleanHDFSFolder $jobTmpDataDir
hadoop fs -mkdir $jobTmpDataDir
hadoop fs -mkdir $path_s0_src_a
hadoop fs -mkdir $path_s0_src_b
hadoop fs -cp $path_input_a"/*" $path_s0_src_a
hadoop fs -cp $path_input_b"/*" $path_s0_src_b
   
cleanHDFSFolder $path_s1_pre_split_a 
step1JobName="${jobDisplayPrefixName}_s1_pre_split"
hadoop streaming \
    -input $path_s0_src_a \
    -output $path_s1_pre_split_a \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixMultiplicationPreSplitMapper.php 200" \
    -reducer "cat" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=10  \
    -jobconf mapred.reduce.tasks=10  \
    -jobconf mapred.job.name="${step1JobName}" 
checkJobStatus $step1JobName $? 
  
cleanHDFSFolder $path_s2_pre_split_b  
step2JobName="${jobDisplayPrefixName}_s2_pre_split"
hadoop streaming \
    -input $path_s0_src_b \
    -output $path_s2_pre_split_b \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixMultiplicationPreSplitMapper.php 100" \
    -reducer "cat" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=10  \
    -jobconf mapred.reduce.tasks=10  \
    -jobconf mapred.job.name="${step2JobName}" 
checkJobStatus $step2JobName $? 
    
cleanHDFSFolder $path_s3_generate_dic_a 
step3JobName="${jobDisplayPrefixName}_s3_generate_dic_a"
hadoop streaming \
    -input $path_s1_pre_split_a \
    -output $path_s3_generate_dic_a \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixRowDicMapper.php -env mapreduce" \
    -reducer "${ENV_HADOOP_PHP_PATH} ./PmatrixRowDicReducer.php -env mapreduce" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=10  \
    -jobconf mapred.reduce.tasks=1  \
    -jobconf mapred.output.key.comparator.class=org.apache.hadoop.mapred.lib.KeyFieldBasedComparator   \
    -jobconf mapred.text.key.comparator.options=-nr  \
    -jobconf mapred.job.name="${step3JobName}"
checkJobStatus $step3JobName $? 
  
cleanHDFSFolder $path_s4_generate_dic_b  
step4JobName="${jobDisplayPrefixName}_s4_generate_dic_b"
hadoop streaming \
    -input $path_s2_pre_split_b \
    -output $path_s4_generate_dic_b \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixRowDicMapper.php -env mapreduce" \
    -reducer "${ENV_HADOOP_PHP_PATH} ./PmatrixRowDicReducer.php -env mapreduce" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=10  \
    -jobconf mapred.reduce.tasks=1  \
    -jobconf mapred.output.key.comparator.class=org.apache.hadoop.mapred.lib.KeyFieldBasedComparator   \
    -jobconf mapred.text.key.comparator.options=-nr  \
    -jobconf mapred.job.name="${step4JobName}"
checkJobStatus $step4JobName $? 
  
cleanHDFSFolder $path_s5_pre_multiplication_a  
step5JobName="${jobDisplayPrefixName}_s5_pre_multiplication_a"
hadoop streaming \
    -input $path_s1_pre_split_a \
    -output $path_s5_pre_multiplication_a \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixMultiplicationPreMapper.php A" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=10  \
    -jobconf mapred.reduce.tasks=1  \
    -jobconf mapred.job.name="${step5JobName}"
checkJobStatus $step5JobName $? 
  
cleanHDFSFolder $path_s6_pre_multiplication_b  
step6JobName="${jobDisplayPrefixName}_s5_pre_multiplication_a"
hadoop streaming \
    -input $path_s2_pre_split_b \
    -output $path_s6_pre_multiplication_b \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixMultiplicationPreMapper.php B" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=10  \
    -jobconf mapred.reduce.tasks=1  \
    -jobconf mapred.job.name="${step6JobName}"
checkJobStatus $step6JobName $? 
  
cleanHDFSFolder $path_s7_multiplication  
step7JobName="${jobDisplayPrefixName}_s7_multiplication"
hadoop streaming \
    -cacheFile "hdfs://namenode.safe.lycc.qihoo.net:9000${path_s3_generate_dic_a}#key_dic_a" \
    -cacheFile "hdfs://namenode.safe.lycc.qihoo.net:9000${path_s4_generate_dic_b}#key_dic_b" \
    -input $path_s5_pre_multiplication_a,$path_s6_pre_multiplication_b \
    -output $path_s7_multiplication \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixMultiplicationMapper.php key_dic_a key_dic_b" \
    -reducer "${ENV_HADOOP_PHP_PATH} ./PmatrixSelfMultiplicationReducer.php -env mapreduce" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=20000  \
    -jobconf mapred.reduce.tasks=1000  \
    -jobconf mapred.job.name="${step7JobName}"
checkJobStatus step7JobName $?      
     
cleanHDFSFolder $path_s8_post_merge  
step8JobName="${jobDisplayPrefixName}_s8_post_merge"
hadoop streaming \
    -input $path_s7_multiplication \
    -output $path_s8_post_merge \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixMultiplicationPostMergeMapper.php -env mapreduce" \
    -reducer "${ENV_HADOOP_PHP_PATH} ./PmatrixMultiplicationPostMergeReducer.php -env mapreduce" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=200  \
    -jobconf mapred.reduce.tasks=200  \
    -jobconf mapred.job.name="${step7JobName}"
checkJobStatus $step8JobName $? 
   
cleanHDFSFolder $path_s9_rs_vectorize  
step9JobName="${jobDisplayPrefixName}_s9_rs_vectorize "
hadoop streaming \
    -input $path_s8_post_merge \
    -output $path_s9_rs_vectorize \
    -mapper "${ENV_HADOOP_PHP_PATH} ./PmatrixRowVectorizeMapper.php -env mapreduce" \
    -reducer "${ENV_HADOOP_PHP_PATH} ./PmatrixRowVectorizeReducer.php -env mapreduce" \
    -file "${codeDirs}" \
    -jobconf mapred.map.tasks=10  \
    -jobconf mapred.reduce.tasks=1  \
    -jobconf mapred.job.name="${step9JobName}"
checkJobStatus $step9JobName $?

   
hadoop fs -mkdir $path_output
hadoop fs -cp $path_s9_rs_vectorize"/*" $path_output

echo "finished ${jobDisplayPrefixName}, output:${path_output}"

