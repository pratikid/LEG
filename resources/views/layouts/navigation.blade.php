                    <!-- Admin Navigation -->
                    @if(auth()->user()->is_admin)
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <x-nav-link :href="route('admin.activity-logs.index')" :active="request()->routeIs('admin.activity-logs.*')">
                                {{ __('Activity Logs') }}
                            </x-nav-link>
                        </div>
                    @endif 