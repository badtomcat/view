<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/12
 * Time: 11:39
 */
$this->extend("layout.php");
?>

<?php $this->start("body")?>
<?php $this->start("foo")?>
foo
<?php $this->end()?>
balbalbal
<?php $this->end()?>
<?php $this->yieldContent("foo")?>


