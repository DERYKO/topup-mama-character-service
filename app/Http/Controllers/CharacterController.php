<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    public $client;

    /**
     * BookController constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    public function index(Request $request)
    {
        $this->validate($request, [
            'book_id' => ['required']
        ]);
        try {
            $response = $this->client->get('https://www.anapioficeandfire.com/api/characters', [
                'params' => $request->all()
            ]);
            $records = json_decode($response->getBody()->getContents());
            $records = collect($records)->filter(function ($character) use ($request) {
                $books = collect($character->books)->filter(function ($book) use ($request) {
                    $link = explode("/", $book);
                    $book_id = $link[count($link) - 1];
                    return $book_id == $request->book_id;
                });
                return count($books) > 0;
            })->toArray();
            return response()->json([
                'message' => 'success fetching characters',
                'data' => $records
            ]);
        } catch (ClientException $e) {
            return response()->json([
                'message' => 'error fetching characters',
                'error' => $e->getMessage()
            ], $e->getCode());
        }
    }
}
