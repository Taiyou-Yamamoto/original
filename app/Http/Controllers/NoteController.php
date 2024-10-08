<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\Book;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }


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

        $rules = [
            'note' => 'required|string|max:500',
            'page_number' => 'nullable|integer|min:1|max:1000'
        ];
        $messages = [
            'note.required' => 'メモを入力してください。',
            'note.max' => 'メモは500文字以下にしてください。',
            'page_number.min' => 'ページ番号がマイナスになっています',
            'page_number.max' => 'ページ番号は1000以下にしてください。',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $validated = $validator->validated();

        // データベースに保存
        Note::create([
            'book_id' => $book->id,
            'user_id' => Auth::user()->id,
            'type' => $book->type,
            'content' => $validated['note'],
            'page_number' => $validated['page_number'] ?? null,
        ]);

        // dd(session()->all());

        return redirect()->route('note.register', ['id' => $id]);
    }


    // ライブラリでの編集
    public function noteEdit(Request $request, $id)
    {
        $rules = [
            'title' => 'required|string|max:100',
            'type' => 'required|string|max:20',
            'image_path' => 'nullable|image',
            'content' => 'required|string|max:500',
            'page_number' => 'nullable|integer|min:1|max:1000'
        ];

        $messages = [
            'title.required' => 'タイトルを入力してください。',
            'type.required' => '種別を入力してください。',
            'type.max' => '種別は20文字以下にしてください。',
            'content.required' => 'メモを入力してください.',
            'content.max' => 'メモは500文字以下にしてください。'

        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $note = Note::with('book')->where('id', $id)->first();

        // dd($validated['page_number']);
        // Noteの内容を更新
        $note->page_number = $validated['page_number'] ?? $note->page_number;
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

        // 各種種別を一つだけ取得
        $types = $notes->pluck('type')->unique();

        return view('note.allNote', compact('notes', 'types'));
    }

    // 検索機能
    public function search(Request $request)
    {

        $id = Auth::user()->id;

        $query = Note::query()->with('book')->where('user_id', $id);
        $types = $query->pluck('type')->unique();

        // 検索された場合
        $keywords = $request->query('keywords');
        $selected_type = $request->query('type');

        if ($request->has('keywords')) {
            $query->where(function ($query) use ($keywords) {
                $query->where('content', 'like', "%{$keywords}%")
                    ->orWhereHas('book', function ($query) use ($keywords) {
                        $query->where('title', 'like', "%{$keywords}%");
                    });
            });
        }

        if ($request->has('type') && $selected_type != 'all') {
            $query->where('type', $selected_type);
        }
        // 検索で条件付け＆ページネーション化
        $notes = $query->orderby('created_at', 'desc')->paginate(10);


        return view('note.allNote', compact('notes', 'types'));
    }

    // 一覧ページでの編集
    public function allNoteEdit(Request $request, $id)
    {
        $rules = [
            'title' => 'required|string|max:100',
            'type' => 'required|string|max:20',
            'image_path' => 'nullable|image',
            'content' => 'required|string|max:500',
            'page_number' => 'nullable|integer|min:1|max:1000'
        ];

        $messages = [
            'title.required' => 'タイトルを入力してください。',
            'type.required' => '種別を入力してください。',
            'type.max' => '種別は20文字以下にしてください。',
            'content.required' => 'メモを入力してください.',
            'content.max' => 'メモは500文字以下にしてください。'

        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $note = Note::with('book')->where('id', $id)->first();

        // Noteの内容を更新
        $note->page_number = $validated['page_number'] ?? null;
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

    // スライド表示
    public function slider()
    {
        $id = Auth::user()->id;
        $notes = Note::with('book')->where('user_id', $id)->orderby('created_at', 'desc')->paginate(10);
        return view('slider/slider', compact('notes'));
    }
}
