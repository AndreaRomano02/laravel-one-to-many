<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Project;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use PhpParser\Node\Stmt\Return_;

class ProjectController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $projects = Project::paginate(10);
    return view('admin.projects.index', compact('projects'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    $project = new Project();
    $types = Type::all();
    return view('admin.projects.create', compact('project', 'types'));
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {

    //! Validazione
    $request->validate([
      'title' => ['required', Rule::unique('projects', 'title')],
      'type_id' => 'nullable|exists:types,id',
      'url' => 'url:http,https|nullable',
      'image' => 'image|nullable',
      'description' => 'required|string',
    ], [
      'title.required' => 'Il titolo è obbligatorio.',
      'type_id.exists' => 'Il campo inserito non esiste nel DB',
      'url.url' => 'l\'url non ininzia con http o https.',
      'image.image' => 'Il file non è un immagine.',
      'description.required' => 'La descrizione è oblligatoria.',
      'description.string' => 'La descrizione deve essere una stringa.',
    ]);

    $data = $request->all();
    if (Arr::exists($data, 'image')) $data['image'] = Storage::putfile('project_image', $data['image']);

    $project = new Project();

    $project->fill($data);

    $project->save();

    return to_route('admin.projects.show', compact('project'))->with('type', 'success')->with('message', 'Il progetto è stato creato con successo!');
  }

  /**
   * Display the specified resource.
   */
  public function show(String $id)
  {
    $project = Project::withTrashed()->findOrFail($id);
    return view('admin.projects.show', compact('project'));
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Project $project)
  {
    $types = Type::all();
    return view('admin.projects.edit', compact('project', 'types'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Project $project)
  {
    //! Validazione
    $request->validate([
      'title' => ['required', Rule::unique('projects', 'title')->ignore($project)],
      'type_id' => 'nullable|exists:types,id',
      'description' => 'required|string',
      'url' => 'url:http,https|nullable',
      'image' => 'image|nullable',
    ], [
      'title.required' => 'Il titolo è obbligatorio.',
      'type_id.exists' => 'Il campo inserito non esiste nel DB',
      'url.url' => 'l\'url non ininzia con http o https.',
      'image.image' => 'Il file non è un immagine.',
      'description.required' => 'La descrizione è oblligatoria.',
      'description.string' => 'La descrizione deve essere una stringa.',
    ]);

    $data = $request->all();

    if (Arr::exists($data, 'image')) {
      if ($project->image) Storage::delete($project->image);
      $data['image'] = Storage::putfile('project_image', $data['image']);
    }

    $project->update($data);
    return to_route('admin.projects.show', compact('project'))->with('type', 'success')->with('message', 'Il progetto è stato modificato con successo!');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Project $project)
  {
    $project->delete();
    return to_route('admin.projects.index')->with('type', 'success')->with('message', 'Il progetto è stato spostato nel cestino!');
  }

  public function trash()
  {
    $projects = Project::onlyTrashed()->get();
    return view('admin.projects.trash', compact('projects'));
  }

  public function restore(String $id)
  {
    $project = Project::onlyTrashed()->findOrFail($id);
    $project->restore();
    return to_route('admin.projects.trash')->with('type', 'success')->with('message', 'Il progetto è stato ripristinato!');
  }

  public function drop(String $id)
  {
    $project = Project::onlyTrashed()->findOrFail($id);
    if ($project->image) Storage::delete($project->image);
    $project->forceDelete();
    return to_route('admin.projects.trash')->with('type', 'success')->with('message', 'Il progetto è stato eliminato definitivamente!');
  }

  public function dropAll()
  {
    Project::onlyTrashed()->forceDelete();
    return to_route('admin.projects.trash')->with('type', 'success')->with('message', 'Il tuo cestino è stato svuotato correttamente!');
  }
}
