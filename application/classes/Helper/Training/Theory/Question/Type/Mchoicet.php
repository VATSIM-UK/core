<?php

defined('SYSPATH') or die('No direct script access.');

class Helper_Training_Theory_Question_Type_Mchoicet {
    public static function prepareOptions(&$request){
        // Let's get all the necessary options from this question!
        $opt = array();
        $opt["question"] = preg_replace("/\<p\>/i", "", $request->post("question"));
        $opt["question"] = preg_replace("/\<\/?p\>/i", "<br />", $opt["question"]);
        $opt["answer_a"] = $request->post("answer_a");
        $opt["answer_b"] = $request->post("answer_b");
        $opt["answer_c"] = $request->post("answer_c");
        $opt["answer_d"] = $request->post("answer_d");
        $opt["answer_correct"] = $request->post("answer_correct");
        return $opt;
    }
}

?>