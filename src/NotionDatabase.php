<?php


namespace Pi\Notion;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Pi\Notion\Exceptions\NotionDatabaseException;
use Pi\Notion\Properties\Property;
use Pi\Notion\Query\Filterable;
use Pi\Notion\Query\MultiSelectFilter;
use Pi\Notion\Traits\ThrowsExceptions;
use Pi\Notion\Traits\RetrieveResource;

class NotionDatabase extends Workspace
{
    use ThrowsExceptions;
    use RetrieveResource;

    private Filterable $filter ;

    private string $id;
    private string $URL;
    private string $created_time;
    private string $last_edited_time;
    private string $title;
    private Collection $properties;
    private Collection $pages;
    private $parentObject;

    public function __construct($id = '', $title = '')
    {
        parent::__construct();
        $this->id = $id ;
        $this->URL = Workspace::DATABASE_URL;
        $this->title = $title;
        $this->properties = new Collection();
        $this->pages = new Collection();
    }


    public function getContents(Collection | Filterable $filters , $id = null, string $sort = '', $filterType = '')
    {
        $id = $id ?? $this->id;
        $queryURL = "$this->URL"."$id"."/query";
        $response = Http::withToken(config('notion-wrapper.info.token'))
            ->post($queryURL,
                [
                    empty($filterType) ? $this->applyFilter($filters) : $this->applyMultipleFilters($filters,$filterType),

                ]
            );
        $this->throwExceptions($response);
        $this->constructObject($response->json());

        return $this;

    }

    public function applyFilter(Filterable $filter): array
    {

       return [
           'filter'=> $filter->setPropertyFilter()
       ];
    }

    public function applyMultipleFilters(Collection $filters, string $filterType): array
    {

        return [
            'filter' => [
            $filterType =>
                $filters->map(function (Filterable $filter){
                    return $filter->setPropertyFilter();
                })
            ]
        ];
    }

    public function sort()
    {
        // TODO, whenever i return objects !
    }

    private function constructObject(mixed $json): self
    {


        if (array_key_exists('results',$json))
        {
            $this->constructPages($json['results']);
            return $this;
        }
        $this->id = $json['id'];
        $this->title = $json['title'][0]['text']['content'];
        $this->constructProperties($json['properties']);
        return $this;

    }

    private function constructPages(mixed $results)
    {
        $pages = collect($results);
        $pages->map(function ($page){

            $this->constructProperties($page['properties']);
            $page = (new NotionPage)->constructObject($page);

            $this->pages->add($page);
        });
    }

    private function constructProperties(mixed $properties)
    {

        $properties = collect($properties);
        $properties->map(function ($property){
            $this->properties->add($property);
        });
    }


}
