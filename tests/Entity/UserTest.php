<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use DateTime;

class UserTest extends TestCase
{
	public function testCreatedAtWithNullValue()
	{
		$user = new User;
		$reflection = new ReflectionClass($user);
		$prop = $reflection->getProperty('createdAt');
		$prop->setAccessible(true);
		$prop->setValue($user, null);
		$this->assertInstanceOf(DateTime::class, $user->getCreatedAt());
	}

	public function testCreatedAtWithNormalValue()
	{
		$user = new User;
		$date = new DateTime();
		$reflection = new ReflectionClass($user);
		$prop = $reflection->getProperty('createdAt');
		$prop->setAccessible(true);
		$prop->setValue($user, $date);
		$this->assertEquals($date, $user->getCreatedAt());
	}
}
