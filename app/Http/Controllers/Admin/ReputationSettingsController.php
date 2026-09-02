<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ReputationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReputationSettingsController extends Controller
{
    /**
     * Each pair: one input controls both the earn key and its matching lose key.
     * 'label'      — what the admin sees
     * 'earn'       — the key that stores a positive value
     * 'lose'       — the key that stores a negative value
     * 'earn_label' — short description shown under the + badge
     * 'lose_label' — short description shown under the − badge
     */
    private const PAIRS = [
        [
            'label'      => 'Ask / Delete a question',
            'earn'       => 'ask_question',
            'earn_label' => 'Awarded when a user publishes a question',
            'lose'       => 'delete_question',
            'lose_label' => 'Deducted when a user deletes their question',
        ],
        [
            'label'      => 'Post / Delete an answer',
            'earn'       => 'post_answer',
            'earn_label' => 'Awarded when a user posts an answer',
            'lose'       => 'delete_answer',
            'lose_label' => 'Deducted when a user deletes their answer',
        ],
        [
            'label'      => 'Answer accepted / Un-accepted',
            'earn'       => 'answer_accepted',
            'earn_label' => 'Awarded when an answer is marked as the accepted solution',
            'lose'       => 'answer_unaccepted',
            'lose_label' => 'Deducted when the accepted status is removed',
        ],
        [
            'label'      => 'Like / Unlike on an answer',
            'earn'       => 'like_answer',
            'earn_label' => 'Awarded to the answer author when someone likes it',
            'lose'       => 'unlike_answer',
            'lose_label' => 'Deducted when a like is removed from an answer',
        ],
        [
            'label'      => 'Like / Unlike on a question',
            'earn'       => 'like_question',
            'earn_label' => 'Awarded to the question author when someone likes it',
            'lose'       => 'unlike_question',
            'lose_label' => 'Deducted when a like is removed from a question',
        ],
    ];

    public function index(): View
    {
        // Load all settings keyed by their key name
        $settings = ReputationSetting::all()->keyBy('key');

        // Build paired rows for the view
        $pairs = collect(self::PAIRS)->map(function ($pair) use ($settings) {
            $earn = $settings->get($pair['earn']);
            return [
                'label'      => $pair['label'],
                'earn_key'   => $pair['earn'],
                'earn_label' => $pair['earn_label'],
                'lose_key'   => $pair['lose'],
                'lose_label' => $pair['lose_label'],
                // Both share the same absolute value — show positive number to admin
                'points'     => $earn ? abs($earn->points) : 0,
            ];
        });

        return view('admin.reputation-settings.index', compact('pairs'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'points'   => 'required|array',
            'points.*' => 'required|integer|min:0|max:999',
        ]);

        $changed = [];

        foreach (self::PAIRS as $pair) {
            if (!isset($request->points[$pair['earn']])) {
                continue;
            }

            $value = (int) $request->points[$pair['earn']];

            // Update the earn key (positive)
            $earnSetting = ReputationSetting::where('key', $pair['earn'])->first();
            if ($earnSetting && $earnSetting->points !== $value) {
                $changed[] = "{$pair['label']}: earn {$earnSetting->points} → +{$value}";
                $earnSetting->update(['points' => $value]);
            }

            // Update the lose key (negative mirror)
            $loseSetting = ReputationSetting::where('key', $pair['lose'])->first();
            if ($loseSetting && $loseSetting->points !== -$value) {
                $changed[] = "{$pair['label']}: lose {$loseSetting->points} → -{$value}";
                $loseSetting->update(['points' => -$value]);
            }
        }

        ReputationSetting::flushCache();

        if (!empty($changed)) {
            AuditLog::create([
                'admin_id' => session('admin_id'),
                'action'   => 'reputation_settings_updated',
                'details'  => 'Reputation point values changed: ' . implode('; ', $changed),
            ]);
        }

        return redirect()->route('admin.reputation-settings.index')
            ->with('success', 'Reputation point values saved. Changes apply to all future actions — past records are unchanged.');
    }
}
