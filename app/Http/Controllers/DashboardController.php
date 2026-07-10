<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $templates     = DocumentType::where('active', true)->orderBy('name')->get();
        $productCount  = Product::count();
        $documentCount = Document::count();
        $recentDocs    = Document::with('documentType')->latest()->take(5)->get();

        return view('dashboard', compact('templates', 'productCount', 'documentCount', 'recentDocs'));
    }
}
