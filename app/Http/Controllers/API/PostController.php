<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Models\Post;
use App\Http\Controllers\API\BaseController as BaseController;

class PostController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $data['posts'] = Post::all();
    
            return $this->sendSuccess($data, 'All Posts Fetched');
        } catch(Exception $e){
            return $this->sendError('Something is wrong with API.', $e->getMessage() );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $postValidation = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                'image' => 'required|mimes:png,jpg,jpeg,gif',
            ]);

            if( $postValidation->fails() ){
                return $this->sendError('Validation Error', $postValidation->errors()->all() );
            }
            $image_name = "";
            $img = $request->image;
            $ext = $img->getClientOriginalExtension();
            $image_name = time() . '.' . $ext;
            $img->move( public_path() . '/uploads', $image_name );

            $post = Post::create([
                'title'       => $request->title,
                'description' => $request->description,
                'image'       => $image_name,
            ]);

            return $this->sendSuccess($post, 'Post Created Successfully');
        } catch(Exception $e){
            return $this->sendError('Something is wrong with API.', $e->getMessage() );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            // $data['post'] = Post::find('id', $id);
            $data['post'] = Post::select(
                'id',
                'title',
                'description',
                'image'
            )->where(['id' => $id])->get();

            if( $data ){
                return $this->sendSuccess($data, 'Post Found');
            }
            else{
                return $this->sendError('This post doesn\'t exist in our record.' );
            }
            
        } catch(Exception $e){
            return $this->sendError('Something is wrong with API.', $e->getMessage() );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            $postValidation = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                'image' => 'required|mimes:png,jpg,jpeg,gif',
            ]);

            if( $postValidation->fails() ){
                return $this->sendError('Something is wrong.', $postValidation->errors()->all() );
            }

            // $post = Post::find('id', $id);
            $post = Post::select('id', 'image')->where(['id' => $id])->get();
            $image_name = "";

            if( !$post || empty($post) || $post == '' || count($post) == 0){
                return $this->sendError('Post not found.');
            }
            
            if( isset($request->image) ){
                if( $request->image != '' && $request->image != null ){
                    $path = public_path() . '/uploads/';
                    if( $post[0]->image != '' && $post[0]->image != null ){
                        $old_file = $path . $post[0]->image;
                        if( file_exists($old_file) ){
                            unlink( $old_file );
                        }
                    }    
                    $img = $request->image;
                    $ext = $img->getClientOriginalExtension();
                    $image_name = time() . '.' . $ext;
                    $img->move( public_path() . '/uploads', $image_name );            
                } 
            } else {
                $image_name = $post[0]->image;
            }

            $updatePost = Post::where(['id' => $id])->update([
                'title'       => $request->title,
                'description' => $request->description,
                'image'       => $image_name,
            ]);
               
            return $this->sendSuccess($updatePost, 'Post has been updated Successfully.');      
        } catch(Exception $e){
            return $this->sendError('Something is wrong with API.', $e->getMessage() );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $post = Post::select('id', 'image')->where(['id' => $id])->get();
            $path = public_path() . '/uploads/';
            
            if( !$post || empty($post) || $post == '' || count($post) == 0){
                return $this->sendError('Post not found.');
            }
            
            if( !empty($post) ){
                if( $post[0]->image != '' && $post[0]->image != null ){
                    $old_file = $path . $post[0]->image;
                    if( file_exists($old_file) ){
                        unlink( $old_file );
                    }
                }
            }

            $deletePost = Post::where(['id' => $id])->delete();
            return $this->sendSuccess($deletePost, 'Post has been deleted successfully.'); 
        } catch(Exception $e){
            return $this->sendError('Something is wrong with API.', $e->getMessage() );
        }
    }
}
