<?php
/**
 * List an map index with descriptions.
 *
 * @param string $this->title
 * @param string $this->section
 * @param SimpleXMLElement $this->map
 * @version SVN: $Id$
 */
$f = new T_Filter_Xhtml();
?>

<h1><?= $f->transform($this->title); ?></h1>

<? foreach ($this->map->link as $item) : ?>
    <h2><a href="<?= $this->url($this->section,(string) $item->alias); ?>">
        <?= $f->transform($item->name); ?></a></h2>
    <p><?= $f->transform($item->desc); ?></p>
<? endforeach; ?>
