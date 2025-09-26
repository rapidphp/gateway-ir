@props([
    'action' => '',
    'method' => 'POST',
    'data' => [],
    'title' => null,
])

<!DOCTYPE html>
<html>
<head>
    <title>Redirecting...</title>
    <script type="text/javascript">
        function autoSubmitForm() {
            var form = document.getElementById('redirectForm');
            if (form) {
                form.submit();
            }
        }

        function trySubmitForm() {
            autoSubmitForm()
            setTimeout(autoSubmitForm, 10000)
        }
    </script>
</head>
<body onload="trySubmitForm();">

<form id="redirectForm" action="{{ $action }}" method="{{ $method }}">
    @php
        // Recursive function to generate hidden inputs
        $generateHiddenInputs = function (array $data, string $prefix = '') use (&$generateHiddenInputs): void {
            foreach ($data as $key => $value) {
                // Construct the full key name (with prefix for nested arrays)
                $fullKey = $prefix ? $prefix . '[' . e($key) . ']' : e($key);

                if (is_array($value)) {
                    // If the value is an array, recurse into it
                    $generateHiddenInputs($value, $fullKey);
                } else {
                    // If the value is scalar, generate a hidden input field
    @endphp
                    <input name="{{ $fullKey }}" value="{{ $value }}" type="hidden">
    @php
                }
            }
        };

        // Call the recursive function to generate inputs
        $generateHiddenInputs($data);
    @endphp

    <p>{{ $title }}</p>
</form>

</body>
</html>
