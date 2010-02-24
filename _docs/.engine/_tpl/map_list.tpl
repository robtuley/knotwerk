<?php
/**
 * List a map of links.
 *
 * @param string $this->section
 * @param SimpleXMLElement $this->map
 * @version SVN: $Id$
 */
$f = new T_Filter_Xhtml();
?>
<ul>
  <? foreach ($this->map->link as $item) : ?>
    <li><a href="<?= $this->url($this->section,(string) $item->alias); ?>">
        <?= $f->transform($item->name); ?></a></li>
  <? endforeach; ?>
</ul>
