<?php /* Smarty version 2.6.0, created on 2010-05-19 18:24:35
         compiled from var.tpl */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'replace', 'var.tpl', 8, false),)), $this); ?>
<?php if (isset($this->_sections['vars'])) unset($this->_sections['vars']);
$this->_sections['vars']['name'] = 'vars';
$this->_sections['vars']['loop'] = is_array($_loop=$this->_tpl_vars['vars']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['vars']['show'] = true;
$this->_sections['vars']['max'] = $this->_sections['vars']['loop'];
$this->_sections['vars']['step'] = 1;
$this->_sections['vars']['start'] = $this->_sections['vars']['step'] > 0 ? 0 : $this->_sections['vars']['loop']-1;
if ($this->_sections['vars']['show']) {
    $this->_sections['vars']['total'] = $this->_sections['vars']['loop'];
    if ($this->_sections['vars']['total'] == 0)
        $this->_sections['vars']['show'] = false;
} else
    $this->_sections['vars']['total'] = 0;
if ($this->_sections['vars']['show']):

            for ($this->_sections['vars']['index'] = $this->_sections['vars']['start'], $this->_sections['vars']['iteration'] = 1;
                 $this->_sections['vars']['iteration'] <= $this->_sections['vars']['total'];
                 $this->_sections['vars']['index'] += $this->_sections['vars']['step'], $this->_sections['vars']['iteration']++):
$this->_sections['vars']['rownum'] = $this->_sections['vars']['iteration'];
$this->_sections['vars']['index_prev'] = $this->_sections['vars']['index'] - $this->_sections['vars']['step'];
$this->_sections['vars']['index_next'] = $this->_sections['vars']['index'] + $this->_sections['vars']['step'];
$this->_sections['vars']['first']      = ($this->_sections['vars']['iteration'] == 1);
$this->_sections['vars']['last']       = ($this->_sections['vars']['iteration'] == $this->_sections['vars']['total']);
 if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['static']):  if ($this->_tpl_vars['show'] == 'summary'): ?>
	static var <?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_name']; ?>
, <?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['sdesc']; ?>
<br>
<?php else: ?>
	<a name="<?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_dest']; ?>
"></a>
	<p></p>
	<h4>static <?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_name']; ?>
 = <span class="value"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_default'])) ? $this->_run_mod_handler('replace', true, $_tmp, "\n", "<br>\n") : smarty_modifier_replace($_tmp, "\n", "<br>\n")))) ? $this->_run_mod_handler('replace', true, $_tmp, ' ', "&nbsp;") : smarty_modifier_replace($_tmp, ' ', "&nbsp;")))) ? $this->_run_mod_handler('replace', true, $_tmp, "\t", "&nbsp;&nbsp;&nbsp;") : smarty_modifier_replace($_tmp, "\t", "&nbsp;&nbsp;&nbsp;")); ?>
</span></h4>
	<p>[line <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['slink']):  echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['slink'];  else:  echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['line_number'];  endif; ?>]</p>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "docblock.tpl", 'smarty_include_vars' => array('sdesc' => $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['sdesc'],'desc' => $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['desc'],'tags' => $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['tags'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

  <br />
	<div class="tags">
  <table border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td><b>Type:</b>&nbsp;&nbsp;</td>
      <td><?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_type']; ?>
</td>
    </tr>
    <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_overrides'] != ""): ?>
    <tr>
      <td><b>Overrides:</b>&nbsp;&nbsp;</td>
      <td><?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_overrides']; ?>
</td>
    </tr>
    <?php endif; ?>
  </table>
	</div><br /><br />
	<div class="top">[ <a href="#top">Top</a> ]</div><br />
<?php endif;  endif;  endfor; endif;  if (isset($this->_sections['vars'])) unset($this->_sections['vars']);
$this->_sections['vars']['name'] = 'vars';
$this->_sections['vars']['loop'] = is_array($_loop=$this->_tpl_vars['vars']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['vars']['show'] = true;
$this->_sections['vars']['max'] = $this->_sections['vars']['loop'];
$this->_sections['vars']['step'] = 1;
$this->_sections['vars']['start'] = $this->_sections['vars']['step'] > 0 ? 0 : $this->_sections['vars']['loop']-1;
if ($this->_sections['vars']['show']) {
    $this->_sections['vars']['total'] = $this->_sections['vars']['loop'];
    if ($this->_sections['vars']['total'] == 0)
        $this->_sections['vars']['show'] = false;
} else
    $this->_sections['vars']['total'] = 0;
if ($this->_sections['vars']['show']):

            for ($this->_sections['vars']['index'] = $this->_sections['vars']['start'], $this->_sections['vars']['iteration'] = 1;
                 $this->_sections['vars']['iteration'] <= $this->_sections['vars']['total'];
                 $this->_sections['vars']['index'] += $this->_sections['vars']['step'], $this->_sections['vars']['iteration']++):
$this->_sections['vars']['rownum'] = $this->_sections['vars']['iteration'];
$this->_sections['vars']['index_prev'] = $this->_sections['vars']['index'] - $this->_sections['vars']['step'];
$this->_sections['vars']['index_next'] = $this->_sections['vars']['index'] + $this->_sections['vars']['step'];
$this->_sections['vars']['first']      = ($this->_sections['vars']['iteration'] == 1);
$this->_sections['vars']['last']       = ($this->_sections['vars']['iteration'] == $this->_sections['vars']['total']);
 if (! $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['static']):  if ($this->_tpl_vars['show'] == 'summary'): ?>
	var <?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_name']; ?>
, <?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['sdesc']; ?>
<br>
<?php else: ?>
	<a name="<?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_dest']; ?>
"></a>
	<p></p>
	<h4><?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_name']; ?>
 = <span class="value"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_default'])) ? $this->_run_mod_handler('replace', true, $_tmp, "\n", "<br>\n") : smarty_modifier_replace($_tmp, "\n", "<br>\n")))) ? $this->_run_mod_handler('replace', true, $_tmp, ' ', "&nbsp;") : smarty_modifier_replace($_tmp, ' ', "&nbsp;")))) ? $this->_run_mod_handler('replace', true, $_tmp, "\t", "&nbsp;&nbsp;&nbsp;") : smarty_modifier_replace($_tmp, "\t", "&nbsp;&nbsp;&nbsp;")); ?>
</span></h4>
	<p>[line <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['slink']):  echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['slink'];  else:  echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['line_number'];  endif; ?>]</p>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "docblock.tpl", 'smarty_include_vars' => array('sdesc' => $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['sdesc'],'desc' => $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['desc'],'tags' => $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['tags'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

  <br />
	<div class="tags">
  <table border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td><b>Type:</b>&nbsp;&nbsp;</td>
      <td><?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_type']; ?>
</td>
    </tr>
    <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_overrides'] != ""): ?>
    <tr>
      <td><b>Overrides:</b>&nbsp;&nbsp;</td>
      <td><?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_overrides']; ?>
</td>
    </tr>
    <?php endif; ?>
  </table>
	</div><br /><br />
	<div class="top">[ <a href="#top">Top</a> ]</div><br />
<?php endif;  endif;  endfor; endif; ?>