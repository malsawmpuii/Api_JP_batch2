<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Post;
use App\Http\Resources\PostResource;

class PostController extends Controller
{
    public function __construct($value='')
    {
        $this->middleware('auth:api')->only('index');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts= Post::all();
        //return $posts;
        return response()->json([
            'status' => 'ok',
            'totalResults' => count($posts),
            'posts'   => PostResource::collection($posts) 
            //collection is for array
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //validation
         $request->validate([
            "title" => 'required|string|min:5',
            "photo" => 'required',
            "category_id" => 'required',
            "content" => 'required'
        ]);

        //if file include
         if($request->file()) {
            $fileName = time().'_'.$request->photo->getClientOriginalName(); // 1970 jan 1
            $filePath = $request->file('photo')->storeAs('post_photo', $fileName, 'public');
            $path = 'storage/'.$filePath;
        }

        // data store
        $post = new Post;
        $post->title = $request->title;
        $post->photo = $path;
        $post->category_id = $request->category_id;
        $post->content = $request->content;
        $post->save();

        //return
        return (new PostResource($post))
                    ->response()
                    ->setStatusCode(201); //data is inserted
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::findOrFail($id);
        return new PostResource($post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // validation
        $request->validate([
            "title" => 'required|string|min:5',
            "photo" => 'required|sometimes',
            "category_id" => 'required',
            "content" => 'required',
            "old_photo" => 'required'
        ]);

        // if file is included, upload
        if($request->file()) {
            $fileName = time().'_'.$request->photo->getClientOriginalName(); // 1970 jan 1
            $filePath = $request->file('photo')->storeAs('post_photo', $fileName, 'public');
            $path = 'storage/'.$filePath;
        }else{
            $path = $request->old_photo;
        }

        // data store
        $post = Post::findOrFail($id);
        $post->title = $request->title;
        $post->photo = $path;
        $post->category_id = $request->category_id;
        $post->content = $request->content;
        $post->save();

        // return
        return (new PostResource($post))
                    ->response()
                    ->setStatusCode(200); //data is updated
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return new PostResource($post);
    }

     public function filterByCategory($category_id)
    {
        $posts = Post::where('category_id',$category_id)->get();

        return response()->json([
            'status' => 'ok',
            'totalResults' => count($posts),
            'posts' => PostResource::collection($posts)
        ]);
    }
}
