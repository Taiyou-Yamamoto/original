<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\Book;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }


    // public function index()
    // {
    //     // 商品一覧取得
    //     $notes = Note::all();

    //     return view('note.index', compact('notes'));
    // }


    // 本登録画面を取得
    public function register($id)
    {
        $book = Book::findOrFail($id);
        $notes = Note::where('book_id', $id)->orderby('created_at', 'desc')->paginate(5);


        return view('note.register', compact('book', 'notes'));
    }

    // メモを追加
    public function add(Request $request, $id)
    {

        $book = Book::findOrFail($id);


        $validated = $request->validate([
            'note' => 'required|string|max:500',
            'page_number' => 'nullable|integer|min:1|max:1000'
        ]);

        // データベースに保存
        Note::create([
            'book_id' => $book->id,
            'user_id' => Auth::user()->id,
            'type' => $book->type,
            'content' => $validated['note'],
            'page_number' => $validated['page_number'] ?? null,
        ]);


        session()->flash('message', '登録しました！');

        return redirect()->route('note.register', ['id' => $id])
            ->with('message', '登録しました！');
    }


    // ライブラリでの編集
    public function noteEdit(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'type' => 'required|string|max:20',
            'page_number' => 'nullable|integer|min:1|max:1000',
            'content' => 'required|string|max:1500'
        ]);

        $note = Note::with('book')->where('id', $id)->first();

        // dd($note);
        // Noteの内容を更新
        $note->page_number = $validated['page_number'];
        $note->content = $validated['content'];
        $note->type = $validated['type'];
        $note->save(); // Noteを保存

        // Bookの内容も更新
        $note->book->title = $validated['title'];
        $note->book->type = $validated['type'];
        $note->book->save(); // Bookを保存

        session()->flash('message', '編集しました！');

        return redirect()->route('note.register', [$note->book->id]);
    }

    // 一覧を開く
    public function allNote(Request $request)
    {
        $id = Auth::user()->id;
        $notes = Note::with('book')->where('user_id', $id)->orderby('created_at', 'desc')->paginate(10);

        return view('note.allNote', compact('notes'));
    }

    // 一覧ページでの編集
    public function allNoteEdit(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'type' => 'required|string|max:20',
            'page_number' => 'nullable|integer|min:1|max:1000',
            'content' => 'required|string|max:1500'
        ]);

        $note = Note::with('book')->where('id', $id)->first();

        // Noteの内容を更新
        $note->page_number = $validated['page_number'];
        $note->content = $validated['content'];
        $note->save(); // Noteを保存

        // Bookの内容も更新
        $note->book->title = $validated['title'];
        $note->book->type = $validated['type'];
        $note->book->save(); // Bookを保存

        session()->flash('message', '編集しました！');

        return redirect()->route('note.allNote');
    }


    // 一冊ごとの削除
    public function destroy($id)
    {
        $note = Note::find($id);
        $book_id = $note->book_id;

        $note->delete();

        return redirect()->route('note.register', ['id' => $book_id]);
    }

    // 一覧の削除
    public function allNoteDestroy($id)
    {
        $note = Note::find($id);
        $note->delete();

        return redirect()->route('note.allNote')->with('message', 'メモが削除されました。');
    }
}
