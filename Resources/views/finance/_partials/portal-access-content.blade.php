    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Select a Company to View as Customer</h3>
                
                <div class="mb-4">
                    <input type="text" id="company-search" placeholder="Search companies..." class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div id="company-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($companies as $company)
                        <a href="{{ route('billing.portal.dashboard', $company) }}" class="company-item block p-6 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-150 ease-in-out" data-name="{{ strtolower($company->name) }}">
                            <h4 class="text-xl font-semibold text-gray-800">{{ $company->name }}</h4>
                            <p class="text-gray-600 mt-2">ID: {{ $company->id }}</p>
                            <p class="text-gray-500 text-sm mt-1">{{ $company->email }}</p>
                        </a>
                    @endforeach
                </div>
                
                <div id="no-results" class="hidden text-center py-4 text-gray-500">
                    No companies found matching your search.
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('company-search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.company-item');
            let hasResults = false;

            items.forEach(item => {
                const name = item.getAttribute('data-name');
                if (name.includes(searchTerm)) {
                    item.style.display = 'block';
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                }
            });
            
            document.getElementById('no-results').style.display = hasResults ? 'none' : 'block';
        });
    </script>
