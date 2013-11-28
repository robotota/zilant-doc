
<span style='display:inline-block;width:300px'>
<div style='height:100px;text-align:center'> Действительно удалить эту запись?</div>
<div>
 <span style='background:#ffa0a0'> <?php print hlink(call_keep($this->metadata->table_name, 'delete', array("id" => $id)),'Да, удалить') ?> </span>
 <span style='background:#aaffaa;float:right'> <?php print hlink(call_return($this->metadata->table_name), 'Нет, не удалять') ?></span></div>
</span>

