<?php

namespace App\Console\Commands;

use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ClassLoader\ClassMapGenerator;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Context;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Serializer as DocBlockSerializer;

class GenerateEloquentMethodPHPDoc extends aCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'ide-helper:eloquent-methods';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate autocompletion for models extending an abstract model';

    protected $abstract_model = aCommand::class;
    protected $eloquent_model = \Illuminate\Database\Eloquent\Model::class;
    protected $scraped_class = \Illuminate\Database\Eloquent\Builder::class;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $_eloquentMethods = (new \ReflectionClass($this->eloquent_model))->getMethods();
        $eloquentMethods = [];
        foreach ($_eloquentMethods as $method) {
            $eloquentMethods[] = $method->getName();
        }

        $name = \Illuminate\Database\Eloquent\Builder::class;
        $reflectionClass = new \ReflectionClass($name);

        $output = '';
        foreach ($reflectionClass->getMethods() as $method)
        {
            if (!starts_with($method->getName(), '__') && array_search($method->getName(), $eloquentMethods) === false) {
                $output .= '* @method ';
                //if ($method->isStatic()) {
                    $output .= 'static ';
                //}
                $tags = (new DocBlock($method))->getTags();

                $params = [];
                $return = '';
                foreach ($tags as $tag) {
                    if ($tag->getName() === 'param') {
                        $parameters = $method->getParameters();
                        $defaultValue = '';
                        foreach ($parameters as $parameter) {
                            if ('$' . $parameter->getName() === $tag->getVariableName()) {
                                try {
                                    $defaultValue .= ' = ' . preg_replace('/\n+|\s+/', '', var_export($parameter->getDefaultValue(), true));
                                } catch (\ReflectionException $e) {}
                            }
                        }

                        $params[$tag->getVariableName() . $defaultValue] = str_contains($tag->getType(), '|') ? 'mixed' : $tag->getType();
                    } elseif ($tag->getName() === 'return' && $tag->getType() !== 'void') {
                        if ($tag->getType() === '$this' || $tag->getType() === 'self') {
                            $return = '\\' . $this->scraped_class;
                        } else {
                            $return = $tag->getType();
                        }
                    }
                }

                if ($return) {
                    $output .= $return . ' ';
                }
                $output .= $method->getName() . '(';
                foreach ($params as $name => $type) {
                    $output .= $type . ' ' . $name . ', ';
                }
                $output = trim($output, ', ') . ')' . PHP_EOL;
            }
        }

        echo $output;
    }
}
