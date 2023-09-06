## Implementation documentation for the 1st task in IPP 2021/2022
### Name and surname: Ivan Tsaireshkin
### Login: xtsiar00
<br />
<br />
<br />

### Task description

The main task is to create a filter script parse.php, that reads the source code in IPPcode22 from STDIN, checks the lexical and syntactic correctness of the code, and prints to STDOUT its XML representation to STDOUT
<br />

### Usage

```
php parse.php <[input file]

or

php parse.php <[input file]> [output file]
```
<br />

### Return codes and errors
* 0 - success
* 10 - missing script parameter (if needed) or use of disabled parameter combination
* 21 - wrong or missing header in the source code written in IPPcode22
* 22 - unknown or incorrect opcode in the source code written in IPPcode22
* 23 - other lexical or syntax error of the source code written in IPPcode22
<br />

### Solution description
Script firstly parses the command line arguments. With the --help flag, brief information about script and usage method will be written out. <br />
* Main function: parser() <br />
After the arguments have been parsed, the main function parser() is run. This function reads  he source file line by line from STDIN in while loop, removes comments and whitespaces, checks if the header is correct, if so, "creates" a DOMDocument and writes XML representation according to the specification. <br />
Parsing of instructions is implemented with switch(), in which, using auxiliary functions, the lexical and syntactical correctness of the incoming code is checked. <br />
When switch() parsed one line, script writes name of opcode and, using a for loop, writes the required number of arguments. In case of an empty string, the script does not write any content. <br />
After parsing the entire source file, main function stops and prints XML output to STDOUT, and the script exits with 0.
* Auxiliary functions: check_type, check_var, check_label, check_symb <br />
These functions, that are using preg_match(), which performs a regular expression matching, check whether a type, var, label or symb is valid. <br />
In case of success, returns an array with the type of the data variable and the contents of the variable, otherwise error 23
