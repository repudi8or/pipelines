<?php

/* this file is part of pipelines */

namespace Ktomk\Pipelines\Runner;

use Ktomk\Pipelines\Cli\ExecTester;
use Ktomk\Pipelines\File\Image;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\Pipelines\Runner\DockerLogin
 */
class DockerLoginTest extends TestCase
{
    public function testCreation()
    {
        $exec = new ExecTester($this);
        $resolver = function () {
        };
        $login = new DockerLogin($exec, $resolver);
        $this->assertInstanceOf('Ktomk\Pipelines\Runner\DockerLogin', $login);
    }

    public function testByImageWithStringImage()
    {
        $exec = new ExecTester($this);
        $resolver = function () {
        };
        $login = new DockerLogin($exec, $resolver);
        $image = new Image('foo/bar');
        $login->byImage($image);
    }

    public function testByImageWithImage()
    {
        $exec = new ExecTester($this);
        $exec->expect('capture', 'docker', 0);
        $resolver = function () {
        };
        $path = __DIR__ . '/../../data/docker-config-no-auth.json';
        $login = new DockerLogin($exec, $resolver, $path);
        $array = array(
            'name' => 'account-name/java:8u66',
            'username' => '$DOCKER_HUB_USERNAME',
            'password' => '$DOCKER_HUB_PASSWORD',
            'email' => '$DOCKER_HUB_EMAIL',
        );
        $image = new Image($array);
        $login->byImage($image);
    }

    public function testDockerLoginHasAuth()
    {
        $exec = new ExecTester($this);
        $resolver = function () {
        };
        $path = __DIR__ . '/../../data/docker-config.json';
        $login = new DockerLogin($exec, $resolver, $path);
        $this->assertTrue($login->dockerLoginHasAuth());
        $this->assertTrue($login->dockerLoginHasAuth('existing.foo.host.example:12345'));
        $this->assertFalse($login->dockerLoginHasAuth('https://repo.foo/'));
    }

    public function testGetDockerConfigPathFromEnvironment()
    {
        $exec = new ExecTester($this);
        $resolver = function () {
        };
        $login = new DockerLogin($exec, $resolver);
        $actual = $login->getDockerConfigPathFromEnvironment();
        $this->assertStringEndsWith('/.docker/config.json', $actual);
    }
}
