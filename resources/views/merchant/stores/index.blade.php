 @extends('layouts.app')

 @section('content')
     <x-common.page-breadcrumb pageTitle="{{ __('titles.stores') }}" />
     <div
         class="min-h-screen rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-12">

         <div class="grid grid-cols-12 gap-4 md:gap-6">
             <div class="col-span-12 space-y-6 xl:col-span-7">
                 <x-ecommerce.stores-metrics :stores="$storeCards" />
             </div>

         </div>
     @endsection
