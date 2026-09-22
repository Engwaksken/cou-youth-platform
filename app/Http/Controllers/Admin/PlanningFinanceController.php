<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PlanningFinanceController extends Controller
{
    public function workPlans(Request $request)
    {
        $query = DB::table('annual_work_plans')->orderByDesc('year')->orderBy('start_date');
        if ($request->filled('year')) $query->where('year', (int) $request->year);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('q')) {
            $q = '%'.$request->q.'%';
            $query->where(fn ($x) => $x->where('title', 'like', $q)->orWhere('objective', 'like', $q)->orWhere('activity', 'like', $q));
        }
        $plans = $query->paginate(12)->withQueryString();
        $stats = [
            'total' => DB::table('annual_work_plans')->count(),
            'active' => DB::table('annual_work_plans')->whereIn('status', ['planned','in_progress'])->count(),
            'completed' => DB::table('annual_work_plans')->where('status', 'completed')->count(),
            'budget' => (float) DB::table('annual_work_plans')->sum('budget'),
        ];
        return view('admin.planning.work-plans', compact('plans','stats'));
    }

    public function storeWorkPlan(Request $request)
    {
        $data = $this->validateWorkPlan($request);
        $data['created_by'] = auth()->id();
        $data['created_at'] = $data['updated_at'] = now();
        DB::table('annual_work_plans')->insert($data);
        return back()->with('success', 'Annual work plan created.');
    }

    public function updateWorkPlan(Request $request, int $id)
    {
        $data = $this->validateWorkPlan($request);
        $data['updated_at'] = now();
        DB::table('annual_work_plans')->where('id', $id)->update($data);
        return back()->with('success', 'Annual work plan updated.');
    }

    public function destroyWorkPlan(int $id)
    {
        DB::table('annual_work_plans')->where('id', $id)->delete();
        return back()->with('success', 'Annual work plan deleted.');
    }

    private function validateWorkPlan(Request $request): array
    {
        return $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'title' => 'required|string|max:180',
            'objective' => 'nullable|string|max:5000',
            'activity' => 'nullable|string|max:5000',
            'responsible_person' => 'nullable|string|max:180',
            'department' => 'nullable|string|max:180',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'progress_percent' => 'nullable|integer|min:0|max:100',
            'status' => 'required|in:planned,in_progress,completed,on_hold,cancelled',
            'notes' => 'nullable|string|max:5000',
        ]);
    }

    public function themes(Request $request)
    {
        $query = DB::table('annual_themes')->orderByDesc('year');
        if ($request->filled('q')) {
            $q = '%'.$request->q.'%';
            $query->where(fn ($x) => $x->where('theme','like',$q)->orWhere('scripture_reference','like',$q));
        }
        $themes = $query->paginate(12)->withQueryString();
        return view('admin.planning.themes', compact('themes'));
    }

    public function storeTheme(Request $request)
    {
        $data = $this->validateTheme($request);
        if ($request->hasFile('image')) $data['image_path'] = $request->file('image')->store('annual-themes', 'public');
        $data['is_published'] = $request->boolean('is_published');
        $data['created_by'] = auth()->id();
        $data['created_at'] = $data['updated_at'] = now();
        DB::table('annual_themes')->insert($data);
        return back()->with('success', 'Annual theme created.');
    }

    public function updateTheme(Request $request, int $id)
    {
        $data = $this->validateTheme($request, $id);
        $current = DB::table('annual_themes')->where('id',$id)->first();
        if ($request->hasFile('image')) {
            if ($current?->image_path) Storage::disk('public')->delete($current->image_path);
            $data['image_path'] = $request->file('image')->store('annual-themes', 'public');
        }
        $data['is_published'] = $request->boolean('is_published');
        $data['updated_at'] = now();
        DB::table('annual_themes')->where('id',$id)->update($data);
        return back()->with('success', 'Annual theme updated.');
    }

    public function destroyTheme(int $id)
    {
        $current = DB::table('annual_themes')->where('id',$id)->first();
        if ($current?->image_path) Storage::disk('public')->delete($current->image_path);
        DB::table('annual_themes')->where('id',$id)->delete();
        return back()->with('success', 'Annual theme deleted.');
    }

    private function validateTheme(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'year' => 'required|integer|min:2020|max:2100|unique:annual_themes,year'.($id ? ','.$id : ''),
            'theme' => 'required|string|max:255',
            'scripture_reference' => 'nullable|string|max:180',
            'description' => 'nullable|string|max:5000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
        ]);
    }

    public function onlineServices(Request $request)
    {
        $query = DB::table('online_services')->orderByDesc('starts_at');
        if ($request->filled('status')) $query->where('status',$request->status);
        if ($request->filled('q')) {
            $q = '%'.$request->q.'%';
            $query->where(fn ($x) => $x->where('title','like',$q)->orWhere('speaker','like',$q)->orWhere('platform','like',$q));
        }
        $services = $query->paginate(12)->withQueryString();
        return view('admin.media.online-services', compact('services'));
    }

    public function storeOnlineService(Request $request)
    {
        $data = $this->validateOnlineService($request);
        if ($request->hasFile('thumbnail')) $data['thumbnail_path'] = $request->file('thumbnail')->store('online-services', 'public');
        $data['created_by'] = auth()->id();
        $data['created_at'] = $data['updated_at'] = now();
        DB::table('online_services')->insert($data);
        return back()->with('success', 'Online service created.');
    }

    public function updateOnlineService(Request $request, int $id)
    {
        $data = $this->validateOnlineService($request);
        $current = DB::table('online_services')->where('id',$id)->first();
        if ($request->hasFile('thumbnail')) {
            if ($current?->thumbnail_path) Storage::disk('public')->delete($current->thumbnail_path);
            $data['thumbnail_path'] = $request->file('thumbnail')->store('online-services', 'public');
        }
        $data['updated_at'] = now();
        DB::table('online_services')->where('id',$id)->update($data);
        return back()->with('success', 'Online service updated.');
    }

    public function destroyOnlineService(int $id)
    {
        $current = DB::table('online_services')->where('id',$id)->first();
        if ($current?->thumbnail_path) Storage::disk('public')->delete($current->thumbnail_path);
        DB::table('online_services')->where('id',$id)->delete();
        return back()->with('success', 'Online service deleted.');
    }

    private function validateOnlineService(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:180',
            'platform' => 'required|string|max:50',
            'stream_url' => 'required|url|max:500',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'speaker' => 'nullable|string|max:180',
            'recurrence' => 'nullable|string|max:40',
            'status' => 'required|in:scheduled,live,completed,cancelled',
            'description' => 'nullable|string|max:5000',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
        ]);
    }

    public function membershipFees(Request $request)
    {
        $query = DB::table('membership_fees as mf')->leftJoin('organisation_units as ou','ou.id','=','mf.organisation_unit_id')
            ->select('mf.*','ou.name as organisation_name')->orderByDesc('mf.year')->orderBy('mf.name');
        if ($request->filled('year')) $query->where('mf.year',(int)$request->year);
        if ($request->filled('q')) $query->where('mf.name','like','%'.$request->q.'%');
        $fees = $query->paginate(12)->withQueryString();
        $organisations = DB::table('organisation_units')->where('is_active',true)->orderBy('name')->get(['id','name']);
        $recentPayments = DB::table('membership_fee_payments as p')->join('membership_fees as f','f.id','=','p.membership_fee_id')
            ->select('p.*','f.name as fee_name')->orderByDesc('p.paid_at')->limit(20)->get();
        $stats = [
            'configured' => DB::table('membership_fees')->count(),
            'collected' => (float) DB::table('membership_fee_payments')->where('status','paid')->sum('amount'),
            'payments' => DB::table('membership_fee_payments')->where('status','paid')->count(),
        ];
        return view('admin.finance.membership-fees', compact('fees','organisations','recentPayments','stats'));
    }

    public function storeMembershipFee(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:180','year'=>'required|integer|min:2020|max:2100','amount'=>'required|numeric|min:0',
            'currency'=>'required|string|size:3','organisation_unit_id'=>'nullable|exists:organisation_units,id','due_date'=>'nullable|date','description'=>'nullable|string|max:5000'
        ]);
        $data['is_active']=$request->boolean('is_active');$data['created_by']=auth()->id();$data['created_at']=$data['updated_at']=now();
        DB::table('membership_fees')->insert($data);
        return back()->with('success','Membership fee created.');
    }

    public function updateMembershipFee(Request $request, int $id)
    {
        $data = $request->validate([
            'name'=>'required|string|max:180','year'=>'required|integer|min:2020|max:2100','amount'=>'required|numeric|min:0',
            'currency'=>'required|string|size:3','organisation_unit_id'=>'nullable|exists:organisation_units,id','due_date'=>'nullable|date','description'=>'nullable|string|max:5000'
        ]);
        $data['is_active']=$request->boolean('is_active');$data['updated_at']=now();
        DB::table('membership_fees')->where('id',$id)->update($data);
        return back()->with('success','Membership fee updated.');
    }

    public function destroyMembershipFee(int $id)
    {
        DB::table('membership_fees')->where('id',$id)->delete();
        return back()->with('success','Membership fee deleted.');
    }

    public function recordMembershipPayment(Request $request)
    {
        $data = $request->validate([
            'membership_fee_id'=>'required|exists:membership_fees,id','member_name'=>'required|string|max:180','member_reference'=>'nullable|string|max:120',
            'amount'=>'required|numeric|min:0.01','currency'=>'required|string|size:3','payment_method'=>'nullable|string|max:50','reference'=>'nullable|string|max:180','paid_at'=>'required|date'
        ]);
        $data['status']='paid';$data['recorded_by']=auth()->id();$data['created_at']=$data['updated_at']=now();
        DB::table('membership_fee_payments')->insert($data);
        return back()->with('success','Membership payment recorded.');
    }

    public function calendar(Request $request)
    {
        $year = (int)($request->year ?: now()->year);
        $items = collect();
        $plans = DB::table('annual_work_plans')->where('year',$year)->whereNotNull('start_date')->get();
        foreach ($plans as $p) $items->push((object)['date'=>$p->start_date,'type'=>'Work Plan','title'=>$p->title,'meta'=>$p->status]);
        $services = DB::table('online_services')->whereYear('starts_at',$year)->get();
        foreach ($services as $s) $items->push((object)['date'=>substr((string)$s->starts_at,0,10),'type'=>'Online Service','title'=>$s->title,'meta'=>$s->platform]);
        if (Schema::hasTable('events') && Schema::hasColumn('events','starts_at')) {
            foreach (DB::table('events')->whereYear('starts_at',$year)->get() as $e) $items->push((object)['date'=>substr((string)$e->starts_at,0,10),'type'=>'Event','title'=>$e->title ?? 'Event','meta'=>$e->venue ?? '']);
        }
        $items = $items->sortBy('date')->values();
        return view('admin.planning.calendar', compact('items','year'));
    }

    public function financialReports(Request $request)
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfMonth();
        $membershipCollected = (float) DB::table('membership_fee_payments')->where('status','paid')->whereBetween('paid_at',[$from->startOfDay(),$to->endOfDay()])->sum('amount');
        $membershipCount = DB::table('membership_fee_payments')->where('status','paid')->whereBetween('paid_at',[$from->startOfDay(),$to->endOfDay()])->count();
        $donations = 0.0; $donationCount = 0;
        if (Schema::hasTable('donations') && Schema::hasColumn('donations','amount')) {
            $dateColumn = Schema::hasColumn('donations','paid_at') ? 'paid_at' : (Schema::hasColumn('donations','created_at') ? 'created_at' : null);
            if ($dateColumn) {
                $dq = DB::table('donations')->whereBetween($dateColumn,[$from->startOfDay(),$to->endOfDay()]);
                if (Schema::hasColumn('donations','status')) $dq->whereIn('status',['paid','completed','successful','success']);
                $donations = (float)(clone $dq)->sum('amount'); $donationCount=(clone $dq)->count();
            }
        }
        $byMethod = DB::table('membership_fee_payments')->selectRaw("COALESCE(payment_method, 'Unspecified') method, SUM(amount) total, COUNT(*) count")
            ->where('status','paid')->whereBetween('paid_at',[$from->startOfDay(),$to->endOfDay()])->groupBy('payment_method')->orderByDesc('total')->get();
        return view('admin.finance.financial-reports', compact('from','to','membershipCollected','membershipCount','donations','donationCount','byMethod'));
    }
}
