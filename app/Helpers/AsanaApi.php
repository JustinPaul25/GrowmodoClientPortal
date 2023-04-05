<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class AsanaApi
{

    protected $accessToken = null;
    var $baseApiUrl = 'https://app.asana.com/api/1.0/';
    var $headers = [];


    public function __construct($accessToken = null)
    {

        if (!empty($accessToken))
            $this->accessToken = $accessToken;
        else
            $this->accessToken = env('ASANA_ACCESS_TOKEN');

        $this->headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function buildHeaders($headers = [])
    {
        return array_merge($this->headers, $headers);
    }

    public function buildApiUrl($uri)
    {
        return $this->baseApiUrl . $uri;
    }

    public function post($uri, $data, $headers = [])
    {
        $response = Http::withHeaders($this->buildHeaders($headers))
            ->post(
                $this->buildApiUrl($uri),
                $data
            );

        return $response->json();
    }

    public function deleteFirstSection($gid, $headers = [])
    {
        $sections = $this->getSections($gid);
        $sections = $sections->json();

        $result = Http::withHeaders($this->buildHeaders($headers))
            ->delete(
                $this->buildApiUrl('sections/' . $sections['data'][0]['gid'])
            );

        return $result->json();
    }

    public function createTask($gid, $data, $headers = [])
    {
        $sections = $this->getSections($gid, []);

        $project = $this->getProject($gid, []);

        $sections = $sections->json();

        $data['data']['assignee_section'] = $sections['data'][0]['gid'];
        $data['data']['workspace'] = $project['data']['workspace']['gid'];

        $response = Http::withHeaders($this->buildHeaders($headers))
            ->post(
                $this->buildApiUrl('tasks'),
                $data
            );

        return $response->json();
    }

    private function getProject($gid, $headers = [])
    {
        $project = Http::withHeaders($this->buildHeaders($headers))
            ->get(
                $this->buildApiUrl('projects/' . $gid)
            );

        return $project->json();
    }

    private function getSections($gid, $headers = [])
    {
        $sections = Http::withHeaders($this->buildHeaders($headers))
            ->get(
                $this->buildApiUrl('projects/' . $gid . '/sections')
            );

        return $sections;
    }
}
