<?php

namespace App\Traits;

trait HasWireElementsPlaceholder
{
    private string|array $placeholderConfig = 'line:classes=w-75|block:ct=mt-4,cb=mb-4|button:classes=mb-0';

    public function placeholder(): string
    {
        $skeleton = $this->getSkeleton();

        $placeholder = '';

        foreach ($skeleton as $element){
            $repeat = $element['repeat'] ?? 1;
            $customCLasses = $element['classes'] ?? '';
            $elementType = $element['type'];

            for ($i = 1; $i <= $repeat; $i++){
                $classes = [];
                // apply classes for first element
                if ($i == 1) {
                    $classes[] .= $element['ct'] ?? '';
                }

                // apply classes for last element
                if ($i == $repeat) {
                    $classes[] .= $element['cb'] ?? '';
                }

                $class = implode(' ', $classes);

                $styles = '';
                if ($elementType == 'block' && isset($element['size'])){
                    $styles .= "height: {$element['size']}";
                }

                $placeholder .= <<<HTML
                    <div class="skeleton-loader mb-2 {$element['type']} $class $customCLasses " style="$styles"></div>
                HTML;
            }
        }

        return <<<HTML
        <form class="py-3 px-3">
            $placeholder
        </form>
        HTML;
    }

    private function getSkeleton(): array
    {
        /*
         * example element properties:
         * type: line,block,button
         * ct: classes to apply on first occurrence
         * cb: classes to apply on last occurrence
         * classes: extra classes to apply on all occurrences of element
         * repeat: occurrences of element
         * */

        $elementTypes = [
            'line',
            'block',
            'button'
        ];

        $parameterTypes = [
            'ct',
            'cb',
            'classes',
            'size'
        ];

        $baseSize = 70;

        $sizes = [
            'lg' => $baseSize * 2,
            'xl' => $baseSize * 3,
            '2xl' => $baseSize * 4,
            '3xl' => $baseSize * 5,
            '4xl' => $baseSize * 6,
            '5xl' => $baseSize * 7,
            '6xl' => $baseSize * 8,
            '7xl' => $baseSize * 9,
        ];

        $config = $this->placeholderConfig();

        if (is_array($config)) {
            return $config;
        }

        $skeleton = [];
        $elements = str($config)->explode('|');

        foreach ($elements as $element){
            $element = str($element);

            $parameters = $element->contains(':') ? $element->after(':') : null;
            $element = $element->before(':');

            $elementType = str($element)->beforeLast('^')->toString();
            $repeat = str($element)->afterLast('^')->toInteger();

            if (!in_array($elementType, $elementTypes)){
                throw new \Exception("$elementType is not a valid Skeleton element");
            }

            $skeletonElement = [
                'type' => $elementType,
                'repeat' => $repeat ?: 1,
            ];

            $parameters = $parameters ? str($parameters)->explode(',') : [];

            foreach ($parameters as $parameter){
                $parameter = str($parameter);
                $paramName = $parameter->before('=')->toString();
                $paramValue = $parameter->after('=')->toString();

                if (!in_array($paramName, $parameterTypes)){
                    throw new \Exception("$paramName is not a valid parameter for $elementType element");
                }

                if ($paramName == 'size'){
                    if ( ! in_array($paramValue, array_keys($sizes))) {
                        throw new \Exception("$paramValue is not a defined size for $elementType element");
                    }

                    $paramValue = $sizes[$paramValue] . 'px';
                }

                $skeletonElement[$paramName] = $paramValue;
            }

            $skeleton[] = $skeletonElement;
        }

        return $skeleton;
    }

    public function placeholderConfig(): array|string
    {
        return $this->placeholderConfig;
    }
}
