#!/bin/bash

if [ ! -n "$ENV_APP_NAME" ]; then
    echo "DEBUG: begin initial sh_loader"
else
    # not need to load twice
    return
fi

# must for loading hadoop env
# cause /home/hdp-ld/.bash_profile could change dir to /home/hdp-ld, change back after
cdir=$PWD
. /home/hdp-ld/.bash_profile
cd $cdir

cd $(dirname $0)

source "../envs/env.ini"

codeDirs="/home/apollo/apps/${ENV_APP_NAME}/src"
binDirs="/home/apollo/apps/${ENV_APP_NAME}/bin"


#transferData from hdfs$1 to local$2 then to remote$3,$4
function transferData()
{
    if [ $# != 4 ] ; then 
        echo "USAGE: \$1 remote hdfs holder" 
        echo "       \$2 local_client_path" 
        echo "       \$3 remote_client_path" 
        echo "       \$4 remote_host" 
        echo "Current is $1 ,$2 ,$3 ,$4"
        exit 1
    fi 
    
    rm -rf $2
    mkdir -p $2
    
    hadoop fs -copyToLocal $1"/*" $2
    
    ssh $4 "rm -rf ${3}"
    ssh $4 "mkdir -p ${3}"
    
    scp -r $2 $4":"$3"/../"
    return 0
}

#clean hdfs folder recursively
function cleanHDFSFolder(){
    if [ $# != 1 ] ; then 
        echo "USAGE: \$1 hdfs folder need to clean" 
        exit 1
    fi 
    
    # check if folder exist
    hadoop fs -test -e $1
    if [ $? -ne 0 ]; then
        echo "DEBUG: HDFS dir ${1} not exists, does not need to clean"
    else
        echo "DEBUG: clean HDFS dir ${1} "
        # delete folder recursively
        hadoop fs -rmr $1
    fi
    return 0
}

function checkJobStatus(){
    if [ $# != 2 ] ; then 
        echo "USAGE: \$1 job name"
        echo "USAGE: \$2 job return value ,integer" 
        exit 1
    fi 
    
    if [ $2 -ne 0 ]; then
        echo "ERROR: job ${1} failed"
        exit;
    else
        echo "DEBUG: job ${1} success "
    fi
    return 0
}