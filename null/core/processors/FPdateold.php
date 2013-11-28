<?php
    class FPdate extends FPDefault{
    
        
        private function getmonthname($number, $state = 0){
            if ($state==0){
                        switch ($number) {
                    case 1:
                        $monthname = "январь";
                        break;
                    case 2:
                        $monthname = "февраль";
                        break;
                    case 3:
                        $monthname = "март";
                        break;
                    case 4:
                        $monthname = "апрель";
                        break;
                    case 5:
                        $monthname = "май";
                        break;
                    case 6:
                        $monthname = "июнь";
                        break;
                    case 7:
                        $monthname = "июль";
                        break;
                    case 8:
                        $monthname = "август";
                        break;
                    case 9:
                        $monthname = "сентябрь";
                        break;
                    case 10:
                        $monthname = "октябрь";
                        break;
                    case 11:
                        $monthname = "ноябрь";
                        break;
                    case 12:
                        $monthname = "декабрь";
                        break;
                    default:
                        $monthname = "ошибкабрь";
                        break;     
                }
                }
                elseif ($state==1)
                       switch ($number) {
                    case 1:
                        $monthname = "января";
                        break;
                    case 2:
                        $monthname = "февраля";
                        break;
                    case 3:
                        $monthname = "марта";
                        break;
                    case 4:
                        $monthname = "апреля";
                        break;
                    case 5:
                        $monthname = "мая";
                        break;
                    case 6:
                        $monthname = "июня";
                        break;
                    case 7:
                        $monthname = "июля";
                        break;
                    case 8:
                        $monthname = "августа";
                        break;
                    case 9:
                        $monthname = "сентября";
                        break;
                    case 10:
                        $monthname = "октября";
                        break;
                    case 11:
                        $monthname = "ноября";
                        break;
                    case 12:
                        $monthname = "декабря";
                        break;
                    default:
                        $monthname = "ошибкабря";
                        break;     
                }
                
                return $monthname;
        }
    
        public function control($year = 0,$month = 0,$day = 0, $append_none = False){
            print "<input type=hidden name = {$this->getControlName()}>";
//            print "<input id={$this->getControlName()} name={$this->getControlName()}>";
//print ' <script>
//    $( "#'.$this->getControlName().'" ).datepicker({dateFormat: "dd.mm.yy"});
//</script>';
            
            print "<select name='{$this->getControlName()}_day'>";
            if ($append_none){
                $is_selected = empty($day)?'selected':'';
                print "<option value='' $is_selected> День не выбран </option>";
            }
            for ($i =1; $i <32; $i++){
                $is_selected = ($i==$day)?'selected':'';
                print "<option value='$i' $is_selected> $i </option>";
                }
            print "</select>";
            
            print "<select name='{$this->getControlName()}_month'>";
            if ($append_none){
                $is_selected = empty($month)?'selected':'';
                print "<option value='' $is_selected> Месяц не выбран </option>";
            }
            for ($i =1; $i <=12; $i++){
                $is_selected = ($i==$month)?'selected':'';
                $monthname=$this->getmonthname($i);
                print "<option value='$i' $is_selected> $monthname </option>";
                }
            print "</select>";
            
            print "<select name='{$this->getControlName()}_year'>";
            if ($append_none){
                $is_selected = empty($year)?'selected':'';
                print "<option value='' $is_selected> Год не выбран </option>";
            }
            for ($i =1900; $i <=2100; $i++){
                $is_selected = ($i==$year)?'selected':'';
                print "<option value='$i' $is_selected> $i </option>";
                }
            print "</select>";
            
            }
        
        public function controlNew(){
        $this->control(0,0,0,True);
        }
        
        public function controlEdit($row){
        if ($row[$this->field->name] == ''||$row[$this->field->name]=='--'){
            $this->control(0,0,0,True);
            }
        else{
            list($year,$month, $day) = split('[/.-]', $row[$this->field->name]);
            $this->control($year,$month,$day,True);
            }
        }
        
//        public function controlFilter($value){
//            list($year,$month, $day) = split('[/.-]', $value);
//            $this->control($year,$month,$day,True);
//        }
        
        public function view($row){
            if ($row[$this->field->name]=='') print('&nbsp');
            else{
            list($year,$month, $day) = split('[/.-]', $row[$this->field->name]);
            if ($year=='0000'||$year==''||$month==0||$month==''||$day==0||$day=='')
                {
                    print('&nbsp');
                }
            else
                {
                    print $day.' '.$this->getmonthname($month,1).' '.$year;
                }
            }
        }
        
        
        public function sanitizeAll($inputparams){
            	       
            $value = $this->sanitize($inputparams[$this->getControlName().'_year']).'-'.$this->sanitize($inputparams[$this->getControlName().'_month']).'-'.$this->sanitize($inputparams[$this->getControlName().'_day']);
            return $value;
    	}
    	
    	public function getCondition($filter_values){
            if (isset($filter_values[$this->getControlName()]))    	    
                return "{$this->getControlName()} like concat(:{$this->getControlName()},'%')";
            else
                return null;    
        }
    	
    	
        
    }
?>