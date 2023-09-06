<?php
/*            IPP 1
*  Ivan Tsiareshkin (xtsiar00)
*  16.03.2022
*/

ini_set('display_errors', 'stderr');

// Bool variable for solving header correctness errors
$header = false;

// Variable for instruction order
$counter = 0;

// Command line arguments parsing
if ($argc == 2) {
    if (array_search("--help", $argv)) {
        echo " * Code parser in IPPcode22\n\n * Usage: php parse.php <[input file]> [output file]\n\n * The filter script reads the source code in IPPcode22 from STDIN,\n checks the lexical and syntactic correctness of the code,\n and prints its XML representation to standart output\n\n";
        exit(0);
    } else {
        echo "10 - missing script parameter (if needed) or use of disabled parameter combination";
        exit(10);
    }
}

// Main function
parser($header, $counter);

exit(0);

/*
* Function checks if <type> is valid.
* In case of success, returns an array with the type of the data variable and the contents of the variable, otherwise error 23
*/
function check_type (string $string, int $number) {
    if (preg_match("/^(int|string|bool)$/", strtolower($string))){
        $vartype[$number] = "type";
        return array($vartype[$number], $string);
    } else {
        echo "23 - other lexical or syntax error of the source code written in IPPcode22";
        exit(23);
    }
}

/*
* Function checks if <var> is valid.
* In case of success, returns an array with the type of the data variable and the contents of the variable, otherwise error 23
*/
function check_var(string $string, int $number) {
    // <var> consists of two separate parts: LF,TF,GF@var_name
    // var_name is a sequence of any alphanumeric and special characters without whitespace beginning with the letter or special character
    // E.g. GF @_x means the variable _x stored in a global framework.
    if (preg_match("/^(LF|TF|GF)@[a-zA-Z\_\-$&%*!?][a-zA-Z\_\-$&%*!?0-9]*$/", $string)){
        $vartype[$number] = "var";
        return array($vartype[$number], $string);
    } else {
        echo "23 - other lexical or syntax error of the source code written in IPPcode22";
        exit(23);
    }
}

/*
* Function checks if <label> is valid.
* In case of success, returns an array with the type of the data variable and the contents of the variable, otherwise error 23
*/
function check_label(string $string, int $number) {
    // <label> is a sequence of any alphanumeric and special characters without whitespace beginning with the letter or special character
    if (preg_match("/^[a-zA-Z\_\-$&%*!?][a-zA-Z\_\-$&%*!?0-9]*$/", $string)){
        $vartype[$number] = "label";
        return array($vartype[$number], $string);
    } else {
        echo "23 - other lexical or syntax error of the source code written in IPPcode22";
        exit(23);
    }
}

/*
* Function checks if <symb> is valid.
* In case of success, returns an array with the type of the data variable and the contents of the variable, otherwise error 23
*/
function check_symb(string $string, int $number) {
    if (preg_match("/^(LF|TF|GF)@[a-zA-Z\_\-$&%*!?][a-zA-Z\_\-$&%*!?0-9]*$/", $string)){
        $vartype[$number] = "var";
        return array($vartype[$number], $string);
    } elseif (preg_match("/^int@[-|+]?[0-9]+$/", $string)) {
        $string = substr($string, strpos($string, "@") + 1);
        $vartype[$number] = "int";
        return array($vartype[$number], $string);
    } elseif (preg_match("/^bool@(true|false)$/", $string)) {
        $string = substr($string, strpos($string, "@") + 1);
        $vartype[$number] = "bool";
        return array($vartype[$number], $string);
    } elseif (preg_match("/^string@(([^\s\#\\\\]|\\\\[0-9]{3})*$)/", $string)) {
        // The literal for the string type is written as a sequence of a constant printable characters in UTF-8 encoding
        // and escape sequences in the form of \xyz,
        // where xyz is a decimal number in the range 000-999 composed of three digits
        $string = substr($string, strpos($string, "@") + 1);
        $vartype[$number] = "string";
        return array($vartype[$number], $string);
    } elseif (preg_match("/^nil@nil$/", $string)) {
        $string = substr($string, strpos($string, "@") + 1);
        $vartype[$number] = "nil";
        return array($vartype[$number], $string);
    } else {
        echo "23 - other lexical or syntax error of the source code written in IPPcode22";
        exit(23);
    }
}

/*
* Main function that parses the source code, checks its lextical and syntactic correctness and creates XML output
* In case of success prints XML output to STDOUT
*/
function parser(bool $header, int $counter) {
    while ($line = fgets(STDIN)) {
        $line = preg_replace("/#.*$/", "", $line);
        $line = preg_replace("/\s+/", " ", trim($line));

        if (strlen($line) == 0) {
            continue;
        }

        if (strtoupper($line) == ".IPPCODE22") {
            if ($header == false) {
                $header = true;
                $output = new DOMDocument("1.0", "UTF-8");
                $output->formatOutput = true;

                $start = $output->createElement("program");
                $start = $output->appendChild($start);

                $language = $output->createAttribute("language");
                $language->value = "IPPcode22";
                $start->appendChild($language);
                continue;
            } else {
                echo "22 - unknown or incorrect opcode in the source code written in IPPcode22";
                exit(22);
            }
        } else {
            if ($header == false) {
                echo "21 - wrong or missing header in the source code written in IPPcode22";
                exit(21);
            }
        }

        $opcode = array("MOVE", "CREATEFRAME", "PUSHFRAME", "POPFRAME", "DEFVAR", "CALL", "RETURN",
        "PUSHS", "POPS", "ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "NOT", "INT2CHAR",
        "STRI2INT", "READ", "WRITE", "CONCAT", "STRLEN", "GETCHAR", "SETCHAR", "TYPE", "LABEL", "JUMP",
        "JUMPIFEQ", "JUMPIFNEQ", "EXIT", "DPRINT", "BREAK");

        $sepline = explode(" ", trim($line, "\n"));
        $sepline[0] = strtoupper($sepline[0]);

        if (in_array($sepline[0], $opcode)) {
            $instruction = strtoupper($sepline[0]);
        } else {
            echo "22 - unknown or incorrect opcode in the source code written in IPPcode22";
            exit(22);
        }

        $instr = $output->createElement("instruction");
        $start->appendChild($instr);
        $order = $output->createAttribute("order");
        $counter += 1;
        $order->value = $counter;
        $instr->appendChild($order);

        switch ($instruction) {
            case "MOVE":
            case "INT2CHAR":
            case "STRLEN":
            case "TYPE":
            case "NOT":
                // <var> <symb>
                if (count($sepline) != 3){
                    echo "23 - other lexical or syntax error of the source code written in IPPcode22";
                    exit(23);
                }
                $arg = 2;
                list($vartype[1], $sepline[1]) = check_var($sepline[1], 1);
                list($vartype[2], $sepline[2]) = check_symb($sepline[2], 2);
                break;

            case "CREATEFRAME":
            case "PUSHFRAME":
            case "POPFRAME":
            case "RETURN":
            case "BREAK":
                if (count($sepline) != 1){
                    echo "23 - other lexical or syntax error of the source code written in IPPcode22";
                    exit(23);
                }
                $arg = 0;
                break;

            case "DEFVAR":
            case "POPS":
                // <var>
                if (count($sepline) != 2){
                    echo "23 - other lexical or syntax error of the source code written in IPPcode22";
                    exit(23);
                }
                $arg = 1;
                list($vartype[1], $sepline[1]) = check_var($sepline[1], 1);
                break;

            case "CALL":
            case "LABEL":
            case "JUMP":
                // <label>
                if (count($sepline) != 2){
                    echo "23 - other lexical or syntax error of the source code written in IPPcode22";
                    exit(23);
                }
                $arg = 1;
                list($vartype[1], $sepline[1]) = check_label($sepline[1], 1);
                break;

            case "PUSHS":
            case "WRITE":
            case "EXIT":
            case "DPRINT":
                // <symb>
                if (count($sepline) != 2){
                    echo "23 - other lexical or syntax error of the source code written in IPPcode22";
                    exit(23);
                }
                $arg = 1;
                list($vartype[1], $sepline[1]) = check_symb($sepline[1], 1);
                break;

            case "ADD":
            case "SUB":
            case "MUL":
            case "IDIV":
            case "LT":
            case "GT":
            case "EQ":
            case "AND":
            case "OR":
            case "STRI2INT":
            case "CONCAT":
            case "GETCHAR":
            case "SETCHAR":
                // <var> <symb> <symb>
                if (count($sepline) != 4){
                    echo "23 - other lexical or syntax error of the source code written in IPPcode22";
                    exit(23);
                }
                $arg = 3;
                list($vartype[1], $sepline[1]) = check_var($sepline[1], 1);
                list($vartype[2], $sepline[2]) = check_symb($sepline[2], 2);
                list($vartype[3], $sepline[3]) = check_symb($sepline[3], 3);
                break;

            case "READ":
                // <var> <type>
                if (count($sepline) != 3){
                    echo "23 - other lexical or syntax error of the source code written in IPPcode22";
                    exit(23);
                }
                $arg = 2;
                list($vartype[1], $sepline[1]) = check_var($sepline[1], 1);
                list($vartype[2], $sepline[2]) = check_type($sepline[2], 2);
                break;

            case "JUMPIFEQ":
            case "JUMPIFNEQ":
                // <label> <symb> <symb>
                if (count($sepline) != 4){
                    echo "23 - other lexical or syntax error of the source code written in IPPcode22";
                    exit(23);
                }
                $arg = 3;
                list($vartype[1], $sepline[1]) = check_label($sepline[1], 1);
                list($vartype[2], $sepline[2]) = check_symb($sepline[2], 2);
                list($vartype[3], $sepline[3]) = check_symb($sepline[3], 3);
                break;
        }

        $opcode = $output->createAttribute("opcode");
        $opcode->value = $instruction;

        $instr->appendChild($opcode);

        // Loop for writing correct number of arguments
        for ($i = 1; $i <= $arg; $i++) {
            $argument = $output->createElement("arg".$i);

            $instr->appendChild($argument);

            $type = $output->createAttribute("type");
            $type->value = $vartype[$i];

            $argument->appendChild($type);
            $var_content = $output->createTextNode($sepline[$i]);
            if (empty($sepline[$i]) && $vartype[$i] == "string") {
                continue;
            }
            $argument->appendChild($var_content);
        }

    }
    echo $output->saveXML();
}

?>
