<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageCard;
use App\Models\PageSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageContentController extends Controller
{
    public function index(Request $request)
    {
        $page = (string) $request->query('page', 'home');
        $slides = PageSlide::where('page', $page)->orderBy('sort_order')->latest('id')->get();
        $cards = PageCard::where('page', $page)->orderBy('sort_order')->latest('id')->get();
        return view('admin.page_content.index', ['page' => $page, 'slides' => $slides, 'cards' => $cards]);
    }

    public function storeSlide(Request $request)
    {
        $data = $request->validate([
            'page' => 'required|string|max:50',
            'title' => 'nullable|string|max:180',
            'subtitle' => 'nullable|string|max:255',
            'media_type' => 'required|in:image,video',
            'media_file' => 'nullable|file|max:10240',
            'media_path' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        if ($request->hasFile('media_file')) {
            $data['media_path'] = $request->file('media_file')->store('slides', 'public');
        }
        unset($data['media_file']);
        $data['is_active'] = $request->boolean('is_active', true);
        PageSlide::create($data);
        return back()->with('success', 'Slide added.');
    }

    public function updateSlide(Request $request, PageSlide $slide)
    {
        $data = $request->validate([
            'page' => 'required|string|max:50',
            'title' => 'nullable|string|max:180',
            'subtitle' => 'nullable|string|max:255',
            'media_type' => 'required|in:image,video',
            'media_file' => 'nullable|file|max:10240',
            'media_path' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        if ($request->hasFile('media_file')) {
            if ($slide->media_path) Storage::disk('public')->delete($slide->media_path);
            $data['media_path'] = $request->file('media_file')->store('slides', 'public');
        }
        unset($data['media_file']);
        $data['is_active'] = $request->boolean('is_active');
        $slide->update($data);
        return back()->with('success', 'Slide updated.');
    }

    public function destroySlide(PageSlide $slide)
    {
        if ($slide->media_path) Storage::disk('public')->delete($slide->media_path);
        $slide->delete();
        return back()->with('success', 'Slide deleted.');
    }

    public function storeCard(Request $request)
    {
        $data = $request->validate([
            'page' => 'required|string|max:50',
            'title' => 'nullable|string|max:180',
            'body' => 'nullable|string|max:2000',
            'icon' => 'nullable|string|max:80',
            'image_file' => 'nullable|file|max:10240',
            'image_path' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        if ($request->hasFile('image_file')) {
            $data['image_path'] = $request->file('image_file')->store('cards', 'public');
        }
        unset($data['image_file']);
        $data['is_active'] = $request->boolean('is_active', true);
        PageCard::create($data);
        return back()->with('success', 'Card added.');
    }

    public function updateCard(Request $request, PageCard $card)
    {
        $data = $request->validate([
            'page' => 'required|string|max:50',
            'title' => 'nullable|string|max:180',
            'body' => 'nullable|string|max:2000',
            'icon' => 'nullable|string|max:80',
            'image_file' => 'nullable|file|max:10240',
            'image_path' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        if ($request->hasFile('image_file')) {
            if ($card->image_path) Storage::disk('public')->delete($card->image_path);
            $data['image_path'] = $request->file('image_file')->store('cards', 'public');
        }
        unset($data['image_file']);
        $data['is_active'] = $request->boolean('is_active');
        $card->update($data);
        return back()->with('success', 'Card updated.');
    }

    public function destroyCard(PageCard $card)
    {
        if ($card->image_path) Storage::disk('public')->delete($card->image_path);
        $card->delete();
        return back()->with('success', 'Card deleted.');
    }
}
