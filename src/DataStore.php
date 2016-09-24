<?php

namespace Previewtechs\GoogleDataStore;


class DataStore
{
    protected $projectId;
    protected $apiKey;
    protected $client;
    protected $datastore;

    protected $query = [];

    protected $namespace = null;

    protected $response;

    public function __construct($config = [])
    {
        $this->projectId = $config['projectId'];

        $this->client = new \Google_Client();
        $this->client->setDeveloperKey($config['apiKey']);
        $this->client->useApplicationDefaultCredentials(true);
        $this->datastore = new \Google_Service_Datastore($this->client);
    }

    public function find($query = [])
    {
        if ($query) {
            $this->setQuery($query);
        }

        $queryRequest = new \Google_Service_Datastore_RunQueryRequest();

        $queryObj = new \Google_Service_Datastore_Query($this->query);
        $queryRequest->setQuery($queryObj);

        if ($this->namespace) {
            $namespaceObj = new \Google_Service_Datastore_PartitionId();
            $namespaceObj->setNamespaceId($this->namespace);

            $queryRequest->setPartitionId($namespaceObj);
        }

        $result = $this->datastore->projects->runQuery($this->projectId, $queryRequest);

        return $this->response = $result->getBatch();
    }

    public function setQuery($query = [])
    {
        $this->query = $query;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    public function getResponse($type = 'array')
    {
        if ($type == 'object') {
            return $this->response->getEntityResults();
        }

        if ($this->response) {
            $data = [];
            foreach ($this->response->getEntityResults() as $entities) {
                $entity = [];
                foreach ($entities->getEntity()->getProperties() as $key => $value) {

                    if ($value->integerValue) {
                        $entity[$key] = $value->integerValue;
                    } else {
                        if ($value->stringValue) {
                            $entity[$key] = $value->stringValue;
                        } else {
                            if ($value->timestampValue) {
                                $entity[$key] = $value->timestampValue;
                            }
                        }
                    }
                }

                $data[] = $entity;
            }

            if ($type == 'json') {
                return json_encode($data);
            } else {
                return $data;
            }

        } else {
            return [];
        }
    }
}