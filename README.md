# HadoopStreamMatrix

Streaming Matrix Multiplication on Hadoop, for Similarly Compute & CF Recommendation

### Quick Start and Examples

#### matrix format on hadoop
matrix format, streaming matrix format as a set of lines, each line represent as a vector represent rows of the Matrix
```json
index_a_1    {"index_b_1":"0.4115","index_b_2":"0.3351"}
index_a_2    {"index_b_3":"0.0212","index_b_5":"0.2312"}
...
```

#### matrix multiplication

Matrix (A,B) X (B,C) = (A,C）

run code
```shell
/bin/sh bin/matrix_multiplication.sh $input_matrix_a_hadoop_dir_path $input_matrix_b_hadoop_dir_path $output_matrix_c_hadoop_dir_path 
```


#### matrix self multiplication

Matrix (A,B) X (B,A) = (A,A)

run code
```shell
/bin/sh bin/matrix_multiplication.sh $input_matrix_hadoop_dir_path $output_matrix_hadoop_dir_path 
```


#### compute principle

reference:http://importantfish.com/two-step-matrix-multiplication-with-hadoop/