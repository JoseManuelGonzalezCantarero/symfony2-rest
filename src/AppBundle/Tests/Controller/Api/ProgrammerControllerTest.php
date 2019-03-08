<?php

namespace AppBundle\Tests\Controller\Api;

use AppBundle\Test\ApiTestCase;

class ProgrammerControllerTest extends ApiTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->createUser('weaverryan');
    }

    public function testPOST()
    {
        $data = array(
            'nickname' => 'ObjectOrienter',
            'avatarNumber' => 5,
            'tagLine' => 'a test dev!'
        );
        // 1) Create a programmer resource
        $response = $this->client->post('/api/programmers', [
            'body' => json_encode($data)
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertStringEndsWith('/api/programmers/ObjectOrienter', $response->getHeader('Location'));
        $finishedData = json_decode($response->getBody(true), true);
        $this->assertArrayHasKey('nickname', $finishedData);
        $this->assertEquals('ObjectOrienter', $finishedData['nickname']);
    }

    public function testGETProgrammer()
    {
        $this->createProgrammer([
            'nickname' => 'UnitTester',
            'avatarNumber' => 3,
        ]);

        $response = $this->client->get('/api/programmers/UnitTester');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, [
            'nickname',
            'avatarNumber',
            'powerLevel',
            'tagLine'
        ]);
        $this->asserter()->assertResponsePropertyEquals($response, 'nickname', 'UnitTester');
    }

    public function testGETProgrammersCollection()
    {
        $this->createProgrammer([
            'nickname' => 'UnitTester',
            'avatarNumber' => 3,
        ]);

        $this->createProgrammer([
            'nickname' => 'CowboyCoder',
            'avatarNumber' => 5,
        ]);

        $response = $this->client->get('/api/programmers');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'programmers');
        $this->asserter()->assertResponsePropertyCount($response, 'programmers', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'programmers[1].nickname', 'CowboyCoder');
    }

    public function testPUTProgrammer()
    {
        $this->createProgrammer([
            'nickname' => 'CowboyCoder',
            'avatarNumber' => 5,
            'tagLine' => 'foo',
        ]);

        $data = [
            'nickname' => 'CowgirlCoder',
            'avatarNumber' => 2,
            'tagLine' => 'foo',
        ];
        $response = $this->client->put('/api/programmers/CowboyCoder', [
            'body' => json_encode($data)
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'avatarNumber', 2);
    }

    public function testPATCHProgrammer()
    {
        $this->createProgrammer([
            'nickname' => 'CowboyCoder',
            'avatarNumber' => 5,
            'tagLine' => 'foo',
        ]);

        $data = [
            'tagLine' => 'bar'
        ];

        $response = $this->client->patch('/api/programmers/CowboyCoder', [
            'body' => json_encode($data)
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'avatarNumber', 5);
        $this->asserter()->assertResponsePropertyEquals($response, 'tagLine', 'bar');
    }

    public function testDELETEProgrammer()
    {
        $this->createProgrammer([
            'nickname' => 'UnitTester',
            'avatarNumber' => 3,
        ]);

        $response = $this->client->delete('/api/programmers/UnitTester');
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testValidationErrors()
    {
        $data = array(
            'avatarNumber' => 2,
            'tagLine' => 'I\'m from a test!'
        );
        // 1) Create a programmer resource
        $response = $this->client->post('/api/programmers', [
            'body' => json_encode($data)
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, [
            'type',
            'title',
            'errors',
        ]);
        $this->asserter()->assertResponsePropertyExists($response, 'errors.nickname');
        $this->asserter()->assertResponsePropertyEquals($response, 'errors.nickname[0]',
            'Please enter a clever nickname');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.avatarNumber');
    }
}