<?php

class FPdictionary extends FPAbstractReference{


    public function controlFilter($value){
        $this->control($value, True);
    }

}

?>