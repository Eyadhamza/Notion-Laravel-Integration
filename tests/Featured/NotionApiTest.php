<?php

namespace Pi\Notion\Tests\Featured;

use Pi\Notion\NotionDatabase;

use Pi\Notion\Exceptions\NotionDatabaseException;
use Pi\Notion\NotionPage;
use Pi\Notion\Tests\TestCase;
use Pi\Notion\Workspace;

class NotionApiTest extends TestCase
{
    /** @test */
    public function it_should_return_database_info()
    {
        $response =  (new NotionDatabase('632b5fb7e06c4404ae12065c48280e4c'))->get();


        $this->assertStringContainsString('database',$response['object']);

        $response =  (new NotionDatabase)->get('632b5fb7e06c4404ae12065c48280e4c');

        $this->assertStringContainsString('database',$response['object']);

        $this->assertStringNotContainsString('error',$response['object']);
    }

    /** @test */
    public function it_should_throw_exception_database_not_found()
    {
        $id = '632b5fb7e06c4404ae12asdasd065c48280e4asdc';

        $this->expectException(NotionDatabaseException::class);

        $response =  (new NotionDatabase($id))->get();


    }
    /** @test */
    public function it_should_throw_exception_database_not_authorized()
    {
        $id = '632b5fb7e06c4404ae12065c48280e4asdc';

        $this->expectException(NotionDatabaseException::class);
        $response =  (new NotionDatabase($id))->get();


    }
    /** @test */
    public function it_should_return_database_contents_with_single_query()
    {
         $filter['property'] = 'Status';
         $filter['select'] = 'Reading';

        $response =  (new NotionDatabase('632b5fb7e06c4404ae12065c48280e4c'))->getContents($filter);


        $this->assertStringContainsString('list',$response['object']);

    }

    /** @test */
    public function it_should_return_database_contents_with_multiple_query()
    {
        $filters[0]['property'] = 'Status';
        $filters[0]['select'] = 'Reading';

        $filters[1]['property'] = 'Publisher';
        $filters[1]['select'] = 'NYT';

        $response =  (new NotionDatabase('632b5fb7e06c4404ae12065c48280e4c'))->getContents($filters,filterType: 'and');


        $this->assertStringContainsString('list',$response['object']);

    }
    /** @test */
    public function it_should_return_page_info()
    {
        $response =  (new NotionPage('834b5c8cc1204816905cd54dc2f3341d'))->get();


        $this->assertStringContainsString('page',$response['object']);


    }

    /** @test */
    public function it_should_return_properties_to_create_page()
    {
        $properties[0]['name'] = 'Name';
        $properties[0]['type'] = 'text';
        $properties[0]['content'] = 'New Media Article';

        $properties[1]['name'] = 'Publisher';
        $properties[1]['type'] = 'select';
        $properties[1]['id'] = '8c4a056e-6709-4dd1-ba58-d34d9480855a';
        $properties[1]['select_name'] = 'Ready to Start';
        $properties[1]['color'] = 'yellow';

        $properties[2]['name'] = 'Status';
        $properties[2]['type'] = 'select';
        $properties[2]['select_name'] = 'The Atlantic';
        $properties[2]['color'] = 'red';
        $response =  (new NotionPage('819f5b54348f463580ef118b6a54bd0d'))->create('632b5fb7e06c4404ae12065c48280e4c',$properties);


        $this->assertStringContainsString('page',$response['object']);


    }


}
