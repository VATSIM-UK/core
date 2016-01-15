<?php

namespace App\Console\Commands;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use ReflectionClass;
use ReflectionException;
use stdClass;

class GenerateEloquentMethodPHPDoc extends aCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ide-helper:eloquent-methods';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate autocompletion for models extending an abstract model';

    protected $abstract_model = aCommand::class;
    protected $eloquent_model = Model::class;
    protected $eloquent_methods = [];
    protected $scraped_class = Builder::class;
    protected $scraped_methods = [];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->getEloquentMethods();

        $scrapedInfo = new ReflectionClass($this->scraped_class);
        foreach ($scrapedInfo->getMethods() as $method)
        {
            if (!$this->isMagicMethod($method) && !$this->isModelMethod($method)) {
                $this->log('* @method static ', null, false);
                $docBlockTags = (new DocBlock($method))->getTags();

                $definition = $this->getMethodDefinition($method, $docBlockTags);

                $this->log("$definition->return ", null, false);

                $this->log(sprintf(
                    '%s(%s)',
                    $method->getName(),
                    implode($definition->params, ', ')
                ));
            }
        }
    }

    protected function getEloquentMethods()
    {
        $eloquentInfo = new ReflectionClass($this->eloquent_model);
        $eloquentMethods = $eloquentInfo->getMethods();
        foreach ($eloquentMethods as $method) {
            $this->eloquent_methods[] = $method->getName();
        }
    }

    protected function isMagicMethod($method)
    {
        return starts_with($method->getName(), '__');
    }

    protected function isModelMethod($method)
    {
        return array_search($method->getName(), $this->eloquent_methods) !== false;
    }

    protected function getMethodDefinition($method, $tags)
    {
        $definition = new stdClass();
        $definition->params = [];
        $definition->return = '';

        foreach ($tags as $tag) {
            if ($tag->getName() === 'param') {
                $definition->params[] = $this->getParameterDefinition($method, $tag);
            } elseif ($tag->getName() === 'return' && $tag->getType() !== 'void') {
                $definition->return = $this->getReturnDefinition($tag);
            }
        }

        return $definition;
    }

    protected function getParameterDefinition($method, $tag)
    {
        $defaultValue = $this->getParameterDefault($method, $tag);

        return (str_contains($tag->getType(), '|') ? 'mixed' : $tag->getType()) . ' ' . $tag->getVariableName() . $defaultValue;
    }

    protected function getParameterDefault($method, $tag)
    {
        $methodParameters = $method->getParameters();
        foreach ($methodParameters as $parameter) {
            if (sprintf('$%s', $parameter->getName()) === $tag->getVariableName()) {
                try {
                    $rawValue = var_export($parameter->getDefaultValue(), true);
                    $value = preg_replace('/\n+|\s+/', '', $rawValue);

                    return sprintf(" = $value");
                } catch (ReflectionException $e) {}
            }
        }

        return '';
    }

    protected function getReturnDefinition($tag)
    {
        if ($tag->getType() === '$this' || $tag->getType() === 'self') {
            return '\\' . $this->scraped_class;
        } else {
            return $tag->getType();
        }
    }
}
