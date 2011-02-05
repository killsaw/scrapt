<?php

class ScraptTest extends PHPUnit_Framework_TestCase
{
     protected $curl;
     protected $basic;

     protected function setUp()
     {
          $this->curl  = new Scrapt_Agent_cURL;
          $this->basic = new Scrapt_Agent_Basic;
     }

     protected function tearDown()
     {
     }

     public function testCURLRequest()
     {
         // Good request.
          $return = $this->curl->request('GET', 'http://killsaw.com');
        
        $this->assertType('array', $return);
        $this->assertEquals($return['headers']['HTTP-status'], 'HTTP/1.1 200 OK');
        $this->assertFalse(empty($return['data']));
        $this->assertContains('<title>killsaw</title>', $return['data']);
        
        // Bad request.
          $return = $this->curl->request('GET', 'http://'.md5('killsaw.com'));
          $this->assertFalse($return);
     }

     public function testBasicRequest()
     {
          // Remove the following lines when you implement this test.
          $this->markTestIncomplete(
             'This test has not been implemented yet.'
          );
     }
}