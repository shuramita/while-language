<?php

global $dictionary;

const _VAR = '_VAR'; $dictionary[_VAR] = function(Token $token){return "$".$token->value;};
const _INTEGER = '_INTEGER'; $dictionary[_INTEGER] = function(Token $token){return $token->value;};
const _SPECIAL_CHAR = '_SPECIAL_CHAR'; $dictionary[_SPECIAL_CHAR] = function(Token $token){return $token->value;};
const _RESERVED_WORD = '_RESERVED_WORD'; $dictionary[_RESERVED_WORD] = function(Token $token){return $token->value;};
const _OPA = '_OPA'; $dictionary[_OPA] = function(Token $token){return $token->value;};
const _OPB = '_OPB'; $dictionary[_OPB] = function(Token $token){return ($token->value==_AND)?"&&":"||";};
const _OPR = '_OPR'; $dictionary[_OPR] = function(Token $token){return $token->value;};

const _PLUS = '+';
const _SUBTRACT = '-';
const _MULTIPLY = '*';
const _DIVISION = '/';

const _END = ';'; $dictionary[_END] = function(Token $token){return ';';};
const _ASSIGN = ':='; $dictionary[_ASSIGN] = function(Token $token){return '=';};
const _GREATER = '>'; ;
const _SMALLER = '<';

const _AND = 'and';
const _OR = 'or';

const Opa = [_MULTIPLY,_DIVISION,_PLUS,_SUBTRACT];
const Opb = [_AND,_OR];
const Opr = [_GREATER,_SMALLER];

const RESERVED_WORDS = ['if','then','while','do','else'];
const SPECIAL_CHARS = ['{','}','(',')'];


const NEW_LINE = 'NEW_LINE';


class Token {
    public $type;
    public $value;
    public function __construct($type,$value)
    {
        $this->type = $type;
        $this->value = $value;
    }
    public function translate(){
        global $dictionary;
        $translateFunction = $dictionary[$this->type];
        return $translateFunction($this);
    }
    static function classify($str){
        $str = strtolower(trim($str));
        //  All variables name will consist of only lowercase letter ('a'-'z') and it's length will not exceed 10.
        if(ctype_lower($str)) {
            if(in_array(strtolower($str),RESERVED_WORDS)) {
                return new Token(_RESERVED_WORD,$str);
            }else{
                return new Token(_VAR, $str);
            }
        }elseif (trim($str) == _ASSIGN) {
            return new Token(_ASSIGN, $str);

        }elseif(is_numeric(trim($str))) {
            return new Token(_INTEGER, (int)$str);

        }elseif (in_array($str,SPECIAL_CHARS)) {
            return new Token(_SPECIAL_CHAR,$str);
        }elseif($str == _END){
            return new Token(_END,$str);
        }elseif(in_array($str,Opa)) {
            return new Token(_OPA,$str);
        }elseif(in_array($str,Opa)) {
            return new Token(_OPA,$str);
        }elseif(in_array($str,Opr)) {
            return new Token(_OPR,$str);
        }
    }
}

class Interpreter {
    public $source;
    public $tokens;
    public $dest;

    function loadSource($source,$type="string"){
        $this->source = $type == 'file' ? file_get_contents($source) : $source;
        return $this;
    }

    public function execute(){
        $this->pre_process();
        $this->translate();

        return $this;
    }

    private function pre_process(){
        // strip all space and new line
        $this->source = str_replace(["\n", "\r\n", "\r"]," ",$this->source);
        $this->source = strtolower($this->source).' ';
    }
    private function post_process(){
        foreach ($this->tokens as $key => $token) {
            $this->dest .= $token->translate().' ';
        }
        $output = str_replace(";",";<br/>",$this->dest);
        $output = str_replace(") do {",") \r\n {\r\n",$output);
        $output = str_replace(") then {",") \r\n {\r\n",$output);
        $output = str_replace("}","; \r\n }\r\n",$output);
        $output = str_replace("<br/>","\r\n", $output);
        $output.= ";";
        // collect all variables


        return $output;
    }
    function translate(){
        $currentIndex = 0;
        foreach (str_split($this->source) as $index => $char) {

            if($char == " " || $char == _END || in_array($char,SPECIAL_CHARS) || in_array($char,Opa) || in_array($char,Opr)) {
//                var_dump('CHAR',$char);
                $stringToken = substr($this->source,$currentIndex,$index-$currentIndex);
                $currentIndex = $index;
//                $stringToken = $tokenString; //str_replace(_END,"",);
//                var_dump($stringToken);
                $token = Token::classify($stringToken);
                if($token) {
                    $this->tokens[] = $token;
                }
            }

        }
//        var_dump($this->tokens);

        return $this;
    }
    private function collect_variable(){
        $variables = [];
        foreach ($this->tokens as $token) {
            if($token->type == _VAR && !in_array($token->value,$variables)) {
               $variables[] = $token->value;
            }
        }
        asort($variables);
        return $variables;
    }
    public function output($toFile=true){
            $output = $this->post_process();
            $variable = $this->collect_variable();
            $output.= "\r\n";
            foreach ($variable as $var) {
                $output.= "echo \"$var $$var \";"."\r\n";
                $output.='echo "\\r\\n";'."\r\n";
            }
            if($toFile === true) {
                file_put_contents('out.php', "<?php \r\n".$output);
            }else{
                eval($output);
            }
            return $this;
    }

    public function close(){

    }

}