<?php
class Example_Block extends Template_Block {
    function content() {
        $example_template = $this->PMDR->getNew('Template',dirname(__FILE__).'/example_block.tpl');
        return $example_template;
    }
}
?>