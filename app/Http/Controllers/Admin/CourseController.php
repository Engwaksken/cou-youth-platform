<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\{Course,Lesson,OrganisationUnit}; use Illuminate\Http\Request;
class CourseController extends Controller {
 public function index(){return view('admin.courses.index',['courses'=>Course::with('lessons')->latest()->paginate(20),'units'=>OrganisationUnit::orderBy('name')->get(['id','name'])]);}
 public function store(Request $r){$d=$r->validate(['title'=>'required|string|max:190','description'=>'nullable|string','organisation_unit_id'=>'nullable|exists:organisation_units,id','age_category'=>'required|in:teen,youth,young_adult,all','is_published'=>'sometimes|boolean']);Course::create([...$d,'is_published'=>$r->boolean('is_published')]);return back()->with('success','Course created.');}
 public function update(Request $r,Course $course){$d=$r->validate(['title'=>'required|string|max:190','description'=>'nullable|string','organisation_unit_id'=>'nullable|exists:organisation_units,id','age_category'=>'required|in:teen,youth,young_adult,all','is_published'=>'sometimes|boolean']);$course->update([...$d,'is_published'=>$r->boolean('is_published')]);return back()->with('success','Course updated.');}
 public function destroy(Course $course){$course->delete();return back()->with('success','Course deleted.');}
 public function storeLesson(Request $r,Course $course){$d=$r->validate(['title'=>'required|string|max:190','body'=>'nullable|string','media_url'=>'nullable|url|max:2048','position'=>'nullable|integer|min:1','is_published'=>'sometimes|boolean']);$course->lessons()->create([...$d,'position'=>$d['position']??($course->lessons()->max('position')+1),'is_published'=>$r->boolean('is_published')]);return back()->with('success','Lesson added.');}
 public function destroyLesson(Course $course,Lesson $lesson){abort_unless($lesson->course_id===$course->id,404);$lesson->delete();return back()->with('success','Lesson deleted.');}
}
