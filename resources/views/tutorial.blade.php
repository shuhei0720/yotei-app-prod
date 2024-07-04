<x-app-layout>
    <x-slot name="header">
    </x-slot>

    <div class="flex justify-center items-center min-h-screen bg-gray-100">
        <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-2xl">
            <div id="tutorial-pages">
                <!-- 各ページのコンテンツ -->
                <div class="tutorial-page" id="page-1">
                    <h2 class="text-2xl font-bold mb-4">ホーム画面でチーム作成・または参加しよう</h2>
                    <img src="{{ asset('img/チーム作成参加.GIF') }}" alt="Tutorial GIF 1" class="mb-4 w-full max-w-sm mx-auto">
                    <div class="flex justify-end">
                        <span class="text-lg font-bold py-3 px-6 mr-10">
                            <span id="current-page">1</span> / <span id="total-pages">4</span>
                        </span>
                        <button onclick="nextPage()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded ml-6">
                            次へ
                        </button>
                    </div>
                </div>
                <div class="tutorial-page" id="page-2" style="display: none;">
                <h2 class="text-2xl font-bold mb-4">チーム画面で予定を作成・編集しよう</h2>
                <img src="{{ asset('img/予定登録編集.GIF') }}" alt="Tutorial GIF 1" class="mb-4 w-full max-w-xs mx-auto">
                    <div class="flex justify-between">
                        <button onclick="prevPage()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            前へ
                        </button>
                        <span class="text-lg font-bold py-3 px-6">
                            <span id="current-page">2</span> / <span id="total-pages">4</span>
                        </span>
                        <button onclick="nextPage()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            次へ
                        </button>
                    </div>
                </div>
                <div class="tutorial-page" id="page-3" style="display: none;">
                <h2 class="text-2xl font-bold mb-4">日付を長押して、過去の予定からクイック登録しよう</h2>
                <img src="{{ asset('img/過去の予定から登録.GIF') }}" alt="Tutorial GIF 1" class="mb-4 w-full max-w-xs mx-auto">
                    <div class="flex justify-between">
                        <button onclick="prevPage()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            前へ
                        </button>
                        <span class="text-lg font-bold py-3 px-6">
                            <span id="current-page">3</span> / <span id="total-pages">4</span>
                        </span>
                        <button onclick="nextPage()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            次へ
                        </button>
                    </div>
                </div>
                <!-- 他のページも同様に追加 -->
                <div class="tutorial-page" id="page-4" style="display: none;">
                    <h2 class="text-2xl font-bold mb-4">プロフィール画面からLINE公式アカウントを友達登録して明日の予定一覧をLINEで受け取ろう。</h2>
                    <img src="{{ asset('img/通知設定.GIF') }}" alt="Tutorial GIF n" class="mb-4 w-full max-w-xs mx-auto">
                    <div class="flex justify-between">
                        <button onclick="prevPage()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            前へ
                        </button>
                        <span class="text-lg font-bold py-3 px-6">
                            <span id="current-page">4</span> / <span id="total-pages">4</span>
                        </span>
                        <form method="POST" action="{{ route('tutorial.complete') }}">
                            @csrf
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                チュートリアルを完了
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const totalPages = document.querySelectorAll('.tutorial-page').length;
        let currentPage = 1;

        document.getElementById('total-pages').textContent = totalPages;

        function updatePageNumber() {
            document.getElementById('current-page').textContent = currentPage;
        }

        function nextPage() {
            document.getElementById(`page-${currentPage}`).style.display = 'none';
            currentPage++;
            document.getElementById(`page-${currentPage}`).style.display = 'block';
            updatePageNumber();
        }

        function prevPage() {
            document.getElementById(`page-${currentPage}`).style.display = 'none';
            currentPage--;
            document.getElementById(`page-${currentPage}`).style.display = 'block';
            updatePageNumber();
        }

        updatePageNumber();
    </script>
</x-app-layout>