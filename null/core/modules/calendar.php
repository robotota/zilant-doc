<?php
class calendar{

    public function do_next(){
        $m = $_GET['m'] + 1;
        $Y = $_GET['Y'];
        if ($m>12){
            $m=1;
            $Y++;
        }    
        print $this->show($m, $Y);
        exit;
    }

    public function do_prev(){
        $m = $_GET['m'] - 1;
        $Y = $_GET['Y'];
        if ($m<=0){
            $m=12;
            $Y--;
        }    
        print $this->show($m, $Y);
        exit;
    }

    
    function show_month($m = '', $Y = ''){
      
        $cm= date('n');
        $cY= date('Y');  
        $months = array("Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");
        $w = date('w',mktime(0, 0, 0, $m, 0, $Y));
        $result = '';   

        $result .= "<table>";
        $result .= "<tr><th>пн</th><th>вт</th><th>ср</th><th>чт</th><th>пт</th><th class=weekend>сб</th><th class=weekend>вс</th></tr><tr>";
        $today = date('j');
        
        // сначала заполнить клетки от 0 до w
        // потом заполнить клетки от 1 до day_count, делая переводы строк в конце недели
        // если i<day_count, то w=0 и начинаем новую строку.
        for ($i = 0; $i < $w; $i++)
            $result .='<td>&nbsp</td>';
        $day_count = date('t', mktime(0,0,0, $m, 1, $Y));    
        for ($i = 1; $i <= $day_count; $i++){
            $styles = '';
            if ($w==5 or $w==6)
                $styles.='weekend ';
            if ($i == $today and $m == $cm and $Y==$cY)
                $styles.='today';
            $result.="<td class='$styles'>$i</td>";
            $w++;
            if ($w == 7 and $i < $day_count){
                $result .= '</tr><tr>';
                $w = 0;
            }
        }
          
        for ($i = $w; $i <7; $i++)
            $result .= "<td>&nbsp</td>";
        $result .= "</tr></table>";
        return $result;
      
    }

    function show($m = '' , $Y = ''){
        if ($m == '')
            $m = date('n');
        if ($Y == '')
            $Y = date('Y');  
        $months = array("Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");

        
        $result .= "<div id=calendar_collection>";
        $result .= "<div class='calendar'>";
        $result .= "<div class='calendar_top'><span id='prev_month' class='invisible button'>&lt&lt</span><span class='monthname'>{$months[$m-1]} $Y</span><span id=next_month class='invisible button'>&gt&gt</span></div>";
        $initial_m = $m;
        $initial_Y = $Y;        
        $result .=$this->show_month($m, $Y);
        $result .= "</div>";
        $m++;        
        if ($m>12){
            $m=1;
            $Y++;
        }    
        $result .= "<div class='invisible calendar additional'>";
        $result .= "<div class='calendar_top'> <span class='monthname'>{$months[$m-1]} $Y</span> </div>";

        $result .=$this->show_month($m, $Y);
        $result .= "</div>";

        $m++;        
        if ($m>12){
            $m=1;
            $Y++;
        }    

        $result .= "<div class='invisible calendar additional'>";
        $result .= "<div class='calendar_top'> <span class='monthname'>{$months[$m-1]} $Y</span></div>";

        $result .=$this->show_month($m, $Y);
        $result .= "</div>";

        $result .= '</div>';
        $result .= "<script>  $('#prev_month').bind('click', function (e){ $.get('/', {module:'calendar', action:'prev', m:$initial_m, Y:$initial_Y}, function(data){ $('#calendar_collection').replaceWith(data);})}); </script> ";
        $result .= "<script> $('#next_month').bind('click', function (e){ $.get('/', {module:'calendar', action:'next', m:$initial_m, Y:$initial_Y}, function(data){ $('#calendar_collection').replaceWith(data);})}); </script> ";
        $result .= '<script> $("#calendar_collection").hover(function(){$(".additional").removeClass("invisible");
                                                                        //$(".calendar .button").removeClass("invisible");
                                                                        $("#calendar_holder2").addClass("wide");},
                                                          function(){$(".additional").addClass("invisible");
                                                                       //$(".calendar .button").addClass("invisible");
                                                                       $("#calendar_holder2").removeClass("wide");}); </script> ';      
        return $result;    
    }
}
?>