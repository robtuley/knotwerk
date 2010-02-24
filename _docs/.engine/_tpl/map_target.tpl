<?php
/**
 * Map target render.
 *
 * @param T_Text_Plain $this->text  formatted text
 *
 *
 * @version SVN: $Id$
 */
$f = new T_Filter_Xhtml();

// render main content
$xhtml = new Xhtml_Text($this->root());
$this->text->accept($xhtml);
$this->buffer($xhtml);
$this->placeholder('features')->capture(); ?>
<ul>
    <? if ($this->next!==false) : ?>
    <li class="next"><a href="<?= $this->url($this->section,(string) $this->next->alias); ?>">NEXT: <?= $f->transform($this->next->name); ?>
        <span><?= $f->transform($this->next->desc); ?></span>
        </a>
    </li>
    <? endif; ?>
    <? if ($this->prev!==false) : ?>
    <li class="prev"><a href="<?= $this->url($this->section,(string) $this->prev->alias); ?>">PREVIOUS: <?= $f->transform($this->prev->name); ?>
        <span><?= $f->transform($this->prev->desc); ?></span>
        </a>
    </li>
    <? endif; ?>
</ul>
<? $this->placeholder('features')->stop(); ?>
