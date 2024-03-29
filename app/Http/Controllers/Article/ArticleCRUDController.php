<?php

namespace App\Http\Controllers\Article;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\FilterRequest;
use App\Http\Requests\Article\StoreRequest;
use App\Http\Requests\Article\UpdateRequest;
use App\Http\Resources\Article\ArticleResource;
use App\Models\Article;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

class ArticleCRUDController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(FilterRequest $request)
    {
        $builder = Article::query();
        $page = $request['page'] ?? 1;
        $perPage = $request['per_page'] ?? 10;
        $builder = $this->serviceFilter->filter($request, $builder);
        $result = $builder->paginate($perPage, ['*'], 'page', $page);

        Log::channel((new Article)->getTable())->info("Пользователь: id({$request->user()->id}) name: {$request->user()->name} = просмотрел список сущностей");
        return ArticleResource::collection($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return ArticleResource
     */
    public function store(StoreRequest $request, Article $id)
    {
        $article = $this->crudService->create($request, $id);

        Log::channel((new Article)->getTable())->info("Пользователь: id({$request->user()->id}) name: {$request->user()->name} = создал новую сущность = {$article}");
        return new ArticleResource($article);
    }

    /**
     * Display the specified resource.
     *
     * @param Article $id
     * @param Request $request
     * @return ArticleResource
     */
    public function show(Article $id, Request $request)
    {
        Log::channel((new Article)->getTable())->info("Пользователь: id({$request->user()->id}) name: {$request->user()->name} = просмотрел сущность: {$id}");
        return new ArticleResource($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param Article $id
     * @return ArticleResource
     * @throws AuthorizationException
     */
    public function update(UpdateRequest $request, Article $id)
    {
        $this->authorize('update', $id);

        $this->crudService->update($request, $id);

        Log::channel((new Article)->getTable())->info("Пользователь: id({$request->user()->id}) name: {$request->user()->name} = обновил сущность {$id}");
        return new ArticleResource($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Article $id
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Article $id, Request $request)
    {
        $this->authorize('delete', $id);

        $this->crudService->destroy($id);

        Log::channel((new Article)->getTable())->info("Пользователь: id({$request->user()->id}) name: {$request->user()->name} = удалил сущность {$id}");
        return response()->json(['message' => 'Файл успешно удалён.'], 200);
    }
}
