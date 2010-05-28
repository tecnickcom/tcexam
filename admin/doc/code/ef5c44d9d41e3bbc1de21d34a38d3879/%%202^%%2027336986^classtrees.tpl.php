<?php /* Smarty version 2.6.0, created on 2010-05-19 18:24:35
         compiled from classtrees.tpl */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl", 'smarty_include_vars' => array('noleftindex' => true)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<h1><?php echo $this->_tpl_vars['title']; ?>
</h1>
<?php if ($this->_tpl_vars['interfaces']):  if (isset($this->_sections['classtrees'])) unset($this->_sections['classtrees']);
$this->_sections['classtrees']['name'] = 'classtrees';
$this->_sections['classtrees']['loop'] = is_array($_loop=$this->_tpl_vars['interfaces']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['classtrees']['show'] = true;
$this->_sections['classtrees']['max'] = $this->_sections['classtrees']['loop'];
$this->_sections['classtrees']['step'] = 1;
$this->_sections['classtrees']['start'] = $this->_sections['classtrees']['step'] > 0 ? 0 : $this->_sections['classtrees']['loop']-1;
if ($this->_sections['classtrees']['show']) {
    $this->_sections['classtrees']['total'] = $this->_sections['classtrees']['loop'];
    if ($this->_sections['classtrees']['total'] == 0)
        $this->_sections['classtrees']['show'] = false;
} else
    $this->_sections['classtrees']['total'] = 0;
if ($this->_sections['classtrees']['show']):

            for ($this->_sections['classtrees']['index'] = $this->_sections['classtrees']['start'], $this->_sections['classtrees']['iteration'] = 1;
                 $this->_sections['classtrees']['iteration'] <= $this->_sections['classtrees']['total'];
                 $this->_sections['classtrees']['index'] += $this->_sections['classtrees']['step'], $this->_sections['classtrees']['iteration']++):
$this->_sections['classtrees']['rownum'] = $this->_sections['classtrees']['iteration'];
$this->_sections['classtrees']['index_prev'] = $this->_sections['classtrees']['index'] - $this->_sections['classtrees']['step'];
$this->_sections['classtrees']['index_next'] = $this->_sections['classtrees']['index'] + $this->_sections['classtrees']['step'];
$this->_sections['classtrees']['first']      = ($this->_sections['classtrees']['iteration'] == 1);
$this->_sections['classtrees']['last']       = ($this->_sections['classtrees']['iteration'] == $this->_sections['classtrees']['total']);
?>
<hr />
<div class="classtree">Root interface <?php echo $this->_tpl_vars['interfaces'][$this->_sections['classtrees']['index']]['class']; ?>
</div><br />
<?php echo $this->_tpl_vars['interfaces'][$this->_sections['classtrees']['index']]['class_tree']; ?>

<?php endfor; endif;  endif;  if ($this->_tpl_vars['classtrees']):  if (isset($this->_sections['classtrees'])) unset($this->_sections['classtrees']);
$this->_sections['classtrees']['name'] = 'classtrees';
$this->_sections['classtrees']['loop'] = is_array($_loop=$this->_tpl_vars['classtrees']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['classtrees']['show'] = true;
$this->_sections['classtrees']['max'] = $this->_sections['classtrees']['loop'];
$this->_sections['classtrees']['step'] = 1;
$this->_sections['classtrees']['start'] = $this->_sections['classtrees']['step'] > 0 ? 0 : $this->_sections['classtrees']['loop']-1;
if ($this->_sections['classtrees']['show']) {
    $this->_sections['classtrees']['total'] = $this->_sections['classtrees']['loop'];
    if ($this->_sections['classtrees']['total'] == 0)
        $this->_sections['classtrees']['show'] = false;
} else
    $this->_sections['classtrees']['total'] = 0;
if ($this->_sections['classtrees']['show']):

            for ($this->_sections['classtrees']['index'] = $this->_sections['classtrees']['start'], $this->_sections['classtrees']['iteration'] = 1;
                 $this->_sections['classtrees']['iteration'] <= $this->_sections['classtrees']['total'];
                 $this->_sections['classtrees']['index'] += $this->_sections['classtrees']['step'], $this->_sections['classtrees']['iteration']++):
$this->_sections['classtrees']['rownum'] = $this->_sections['classtrees']['iteration'];
$this->_sections['classtrees']['index_prev'] = $this->_sections['classtrees']['index'] - $this->_sections['classtrees']['step'];
$this->_sections['classtrees']['index_next'] = $this->_sections['classtrees']['index'] + $this->_sections['classtrees']['step'];
$this->_sections['classtrees']['first']      = ($this->_sections['classtrees']['iteration'] == 1);
$this->_sections['classtrees']['last']       = ($this->_sections['classtrees']['iteration'] == $this->_sections['classtrees']['total']);
?>
<hr />
<div class="classtree">Root class <?php echo $this->_tpl_vars['classtrees'][$this->_sections['classtrees']['index']]['class']; ?>
</div><br />
<?php echo $this->_tpl_vars['classtrees'][$this->_sections['classtrees']['index']]['class_tree']; ?>

<?php endfor; endif;  endif;  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>