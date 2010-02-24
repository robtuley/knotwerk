<?php
/**
 * QA container.
 *
 * @param SimpleXMLElement $this->log  unit test log
 * @param bool $this->green
 * @version SVN: $Id$
 */
$f = new T_Filter_Xhtml();
$date = new T_Filter_DateStr('H:m:s d-m-Y');
?>

<h1>Unit Test Report</h1>

<? if ($this->green) : ?>
<div class="success">
<p>The last executed unit test suite passed, which suggests the build is stable. Note that the last run may just be a partial test, and it is recommended you run your own tests on your deployment/development environment.</p>
</div>

<? else : ?>

<div class="error">
<p>1 or more tests either failed or resulted in an error the last time the test suite was executed. This suggests there is a problem within the codebase or in the configuration of the development/deployment environment which should be investigated.</p>
</div>

<? endif;
// build array of tests
$data = array();
foreach ($this->log->test as $test) {
    $data[(int) $test['date']] = array( 'passed' => (int) $test->passed,
				      'skipped' => (int) $test->skipped,
				      'failed' => (int) $test->failed,
				      'errored' => (int) $test->errored);
}
krsort($data);
$recent = reset($data);
if (false!==$recent) : ?>

<p>The most recent set of unit tests executed on <?= $date->transform(key($data)) ?>: <?= $recent['passed']; ?> tests passed, <?= $recent['skipped']; ?> skipped, <?= $recent['failed']; ?> failed and <?= $recent['errored']; ?> resulted in an error.</p>

<h2>History</h2>

<table>
    <thead>
	<tr>
	    <th>Date</th>
	    <th>Passed</th>
	    <th>Skipped</th>
	    <th>Failed</th>
	    <th>Errored</th>
	</tr>
    </thead>
    <tbody>
	<? foreach ($data as $when => $test) : ?>
	<tr<?= ($test['errored']>0 || $test['failed']>0) ? ' class="failed"' : ''; ?>>
	    <td><?= $date->transform($when); ?></td>
	    <td><?= $test['passed']; ?></td>
	    <td><?= $test['skipped']; ?></td>
	    <td><?= $test['failed']; ?></td>
	    <td><?= $test['errored']; ?></td>
	</tr>
	<? endforeach; ?>
    </tbody>
</table>

<? endif; ?>
