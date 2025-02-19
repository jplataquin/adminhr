<button {{ $attributes->merge(['type' => 'button', 'class' => 'bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 text-gray-700 dark:text-gray-300 text-white hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center']) }}>
    {{ $slot }}
</button>
