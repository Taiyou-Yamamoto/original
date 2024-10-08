@extends('adminlte::page')

@section('title', 'メモを書く')

@section('content_header')

@stop

@section('content')
    <!-- メモ一覧 -->
    <h1 class="text-center p-9 text-6xl font-extrabold gray_shadow ">MEMO一覧</h1>
    <div class="w-full">

        <div id="contextmenu">

        </div>
        {{-- 検索フォーム --}}
        <div class="text-center mb-8">
            <form class="flex justify-center items-center h-12" id="searchform1" method="get"
                action="{{ route('allNote.search') }}">
                <input class="h-full w-3/12 px-4 py-2 border border-gray-300 rounded-l-md active:border-none" type="text"
                    name="keywords" id="keywords" placeholder="キーワードで検索" value="{{ request()->query('keywords') }}" />
                <select id="search" name='type'
                    class="h-full xl:px-7 py-2 border border-gray-300 text-gray-500 active:border-none">
                    <option value="all" @if (request()->query('type') == 'all') selected @endif>全種別</option>
                    @forelse ($types as $type)
                        <option value="{{ $type }}" @if (request()->query('type') == $type) selected @endif>
                            {{ $type }}</option>
                    @empty
                    @endforelse
                </select>
                <button class="h-full px-4 py-2 bg-amber-100 rounded-r-md border gray_shadow active:bg-amber-300 animation"
                    type="submit" value=""><i class="fas fa-search text-lg"></i></button>
            </form>
        </div>

        {{-- テーブル --}}
        <table class='w-full border-gray-300 table-fixed h-full table-striped  shadow gradient-underline::before'>
            <thead>
                <tr class="border-b border-gray-300">
                    <th class="hidden">id</th>
                    <th class="hidden">タイトル</th>
                    <th class="w-8/12 text-center py-2 text-2xl">メモ</th>
                    <th class="w-1/12 text-center">ページ</th>
                    <th class="w-1/12 text-center">種別</th>
                    <th class="w-1/12 py-2 px-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($notes as $note)
                    {{-- タイトル --}}
                    <tr class="text-lg bg-white border-b border-gray-200 md:h-16 xl:h-24 2xl:h-[5rem] 3xl:h-20">
                        <td class="hidden">

                            {{ $note->book->title }}</td>

                        {{-- メモ内容 --}}
                        <td class="py-2 px-4 2xl:text-ms text-ellipsis overflow-hidden whitespace-nowrap">
                            {{ $note->content }}</td>

                        {{-- ページ番号 --}}
                        <td class="py-2 px-4 2xl:text-2xl text-center">{{ $note->page_number }}</td>

                        {{-- 種類 --}}
                        <td class="py-2 px-4 2xl:text-xl text-center text-ellipsis overflow-hidden whitespace-nowrap">
                            {{ $note->book->type }}</td>

                        {{-- 編集ボタン --}}
                        <td class="text-white cursor-pointer"><button id="{{ $loop->index }}" type="button"
                                class="btn shadow-md gray_shadow text-white font-semibold lg:text-lg sm:text-sm py-1 lg:!px-4 !px-1 hover:shadow-none bg-green-500 hover:bg-green-600 rounded-md animation"
                                data-toggle="modal" data-target="#exampleModal" data_id="{{ $note->id }}"
                                data_title="{{ $note->book->title }}" data_type="{{ $note->book->type }}"
                                data_page_number="{{ $note->page_number }}" data_content="{{ $note->content }}"
                                onclick="openEditModal(this)"><i class="fas fa-pencil-alt gray_shadow"></i>
                                編集
                            </button>

                        </td>
                        {{-- 削除ボタン --}}
                        <td class="text-white">
                            <form action="{{ route('allNote.destroy', $note->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="font-semibold lg:text-lg sm:text-sm py-1 lg:!px-4 !px-1 shadow-md hover:shadow-none gray_shadow bg-rose-400 hover:bg-rose-600 cursor-pointer rounded-md animation"><i
                                        class="fas fa-trash gray_shadow"></i> 削除</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white w-full">
                        <td colspan="4" class="w-full text-center py-2 px-4 border border-gray-200">メモがありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- ページネーション --}}
        <div class="mx-auto 2xl:mt-[2rem] ">
            {{ $notes->onEachSide(2)->links('vendor.pagination.tailwind2') }}
        </div>

        {{-- モーダル  --}}
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered w-11/12 h-full max-w-5xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"></h5>
                    </div>

                    {{-- 入力フォーム --}}
                    <form id="editForm" action="" method="POST"> {{-- actionはjsで渡す --}}
                        @csrf
                        @method('PUT')
                        <div class="modal-body h-96 mb-3">
                            <input type="hidden" id="modal_id" name="id">
                            <div class="grid grid-custom gap-4">
                                {{-- モーダルタイトル --}}
                                <div class="mb-4 flex flex-col">
                                    <label for="modal_title"
                                        class="gray_shadow block text-center text-lg font-medium">本のタイトル</label>
                                    <input type="text" name="title" id="modal_title" value="{{ old('title') }}"
                                        class="w-full h-15 p-2 text-xl mx-auto shadow-md border rounded text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    @error('title')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- フォームのコンテナ --}}
                                <div class="grid child-grid-custom  gap-4">

                                    {{-- 種類 --}}
                                    <div class="mb-4 flex flex-col">
                                        <label for="modal_type"
                                            class="gray_shadow block text-center text-lg font-medium">種別</label>
                                        <input type="text" name="type" id="modal_type" value="{{ old('type') }}"
                                            class="w-full h-15 p-2 text-xl mx-auto shadow-md border rounded text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        @error('type')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- ページ番号 --}}
                                    <div class="mb-4 flex flex-col">
                                        <label for="modal_page_number"
                                            class="gray_shadow block text-center text-lg font-medium">ページ番号</label>
                                        <input type="number" name="page_number" id="modal_page_number"
                                            value="{{ old('page_number') }}"
                                            class="w-full h-15 p-2 text-xl mx-auto shadow-md border rounded text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        @error('page_number')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                            </div>
                            {{-- メモ内容 --}}
                            <textarea name="content" id="modal_content" cols="30" rows="6"
                                class="w-full shadow border-teal-300 text-xl rounded-md leading-10 resize-none"></textarea>
                            @if ($errors->has('content'))
                                <li class="list-none text-red-600">{{ $errors->first('content') }}</li>
                            @endif
                        </div>
                        <div class="modal-footer flex flex-row">

                            <button type="submit" class="btn btn-primary font-bold w-1/6 mr-[20rem]">保存</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    @stop

    @section('css')
        @vite(['resources/css/home.css'])
    @stop

    @section('js')
        <script>
            let openEditModal = function(button) {
                let data_id = button.getAttribute('data_id');
                let data_title = button.getAttribute('data_title');
                let data_content = button.getAttribute('data_content');
                let data_type = button.getAttribute('data_type');
                let data_page_number = button.getAttribute('data_page_number');



                document.getElementById('modal_id').value = data_id;
                document.getElementById('modal_title').value = data_title;
                document.getElementById('modal_type').value = data_type;
                document.getElementById('modal_page_number').value = data_page_number;
                document.getElementById('modal_content').value = data_content;

                // url変更
                document.getElementById('editForm').action = `/allNote/edit/${data_id}`

            }
        </script>
    @stop
