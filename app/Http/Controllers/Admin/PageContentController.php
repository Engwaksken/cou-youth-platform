<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageCardRequest;
use App\Http\Requests\PageSlideRequest;
use App\Models\PageCard;
use App\Models\PageSlide;
use App\Models\PageText as PageTextModel;
use App\Services\Access\HierarchyScopeService;
use App\Support\PageText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PageContentController extends Controller
{
    public function index(Request $request, HierarchyScopeService $scope)
    {
        $pages = PageText::pages();
        $page = (string) $request->query('page', 'home');
        if (! array_key_exists($page, $pages)) {
            $page = 'home';
        }

        $slides = PageSlide::where('page', $page)->orderBy('sort_order')->latest('id')->get();
        $cards = PageCard::where('page', $page)->orderBy('sort_order')->latest('id')->get();

        $overrides = app(PageText::class)->overrides($page);
        $textFields = collect($pages[$page]['fields'])
            ->map(fn (array $field, string $key): array => $field + [
                'key' => $key,
                'type' => $field['type'] ?? 'text',
                'group' => $field['group'] ?? $pages[$page]['label'],
                'value' => $overrides[$key] ?? $field['default'],
                'customised' => array_key_exists($key, $overrides),
            ])
            ->groupBy('group');

        return view('admin.page_content.index', [
            'page' => $page,
            'pages' => $pages,
            'slides' => $slides,
            'cards' => $cards,
            'textFields' => $textFields,
            'customisedCount' => count($overrides),
            'canEditText' => $scope->isSuperAdmin($request->user()),
            // The footer is a shared strip, not a page with slides or cards.
            'hasMedia' => $page !== 'footer',
        ]);
    }

    /** Save the wording for one page. Empty, unchanged or reset fields fall back to the default. */
    public function updateTexts(Request $request, string $page): RedirectResponse
    {
        $pages = PageText::pages();
        abort_unless(array_key_exists($page, $pages), 404);
        $fields = $pages[$page]['fields'];

        $rules = ['reset' => ['nullable', 'array']];
        foreach ($fields as $key => $field) {
            $rules["texts.{$key}"] = ['nullable', 'string', ($field['type'] ?? 'text') === 'textarea' ? 'max:2000' : 'max:255'];
        }
        $data = $request->validate($rules);

        $reset = array_keys(array_filter((array) ($data['reset'] ?? [])));

        DB::transaction(function () use ($fields, $data, $reset, $page, $request): void {
            foreach ($fields as $key => $field) {
                $value = str_replace("\r\n", "\n", trim((string) ($data['texts'][$key] ?? '')));

                if (in_array($key, $reset, true) || $value === '' || $value === $field['default']) {
                    PageTextModel::query()->where('page', $page)->where('key', $key)->delete();

                    continue;
                }

                PageTextModel::query()->updateOrCreate(
                    ['page' => $page, 'key' => $key],
                    ['value' => $value, 'updated_by' => $request->user()?->id],
                );
            }
        });

        app(PageText::class)->forget($page);

        return redirect()
            ->route('admin.page-content.index', ['page' => $page])
            ->with('success', $pages[$page]['label'].' page text saved.');
    }

    public function storeSlide(PageSlideRequest $request)
    {
        $data = $request->validated();
        unset($data['media_path']);
        if ($request->hasFile('media_file')) {
            $data['media_path'] = $request->file('media_file')->store('slides', 'public');
        }
        unset($data['media_file']);
        $data['is_active'] = $request->boolean('is_active', true);
        PageSlide::create($data);
        return back()->with('success', 'Slide added.');
    }

    public function updateSlide(PageSlideRequest $request, PageSlide $slide)
    {
        $data = $request->validated();
        unset($data['media_path']);
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

    public function storeCard(PageCardRequest $request)
    {
        $data = $request->validated();
        unset($data['image_path']);
        if ($request->hasFile('image_file')) {
            $data['image_path'] = $request->file('image_file')->store('cards', 'public');
        }
        unset($data['image_file']);
        $data['is_active'] = $request->boolean('is_active', true);
        PageCard::create($data);
        return back()->with('success', 'Card added.');
    }

    public function updateCard(PageCardRequest $request, PageCard $card)
    {
        $data = $request->validated();
        unset($data['image_path']);
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
