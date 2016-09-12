<?php

require_once('simpletest/autorun.php');
require_once('UnitTestBase.php');
require_once('../Util/JsonParser.php'); //Objektum kiíráshoz van rá szükség

define ('SERVICE_ROOT', 'http://localhost:8001/karitasz/Controls/');
//ini_set('max_execution_time', 300);
require_once('CodeControls.php');
require_once('CustomerControls.php');
require_once('OperationControls.php');
require_once('TransportControls.php');
/*
assertTrue($x)	Fail unless $x evaluates true
assertFalse($x)	Fail unless $x evaluates false
assertNull($x)	Fail unless $x is not set
assertNotNull($x)	Fail unless $x is set to something
assertIsA($x, $t)	Fail unless $x is the class or type $t
assertNotA($x, $t)	Fail unless $x is not the class or type $t
assertEqual($x, $y)	Fail unless $x == $y is true
assertNotEqual($x, $y)	Fail unless $x == $y is false
assertWithinMargin($x, $y, $margin)	Fail unless $x and $y are separated less than $margin
assertOutsideMargin($x, $y, $margin)	Fail unless $x and $y are sufficiently different
assertIdentical($x, $y)	Fail unless $x === $y for variables, $x == $y for objects of the same type
assertNotIdentical($x, $y)	Fail unless $x === $y is false, or two objects are unequal or different types
assertReference($x, $y)	Fail unless $x and $y are the same variable
assertCopy($x, $y)	Fail unless $x and $y are the different in any way
assertSame($x, $y)	Fail unless $x and $y are the same objects
assertClone($x, $y)	Fail unless $x and $y are identical, but separate objects
assertPattern($p, $x)	Fail unless the regex $p matches $x
assertNoPattern($p, $x)	Fail if the regex $p matches $x
expectError($e)	Triggers a fail if this error does not happen before the end of the test
expectException($e)	Triggers a fail if this exception is not thrown before the end of the test
*/
?>