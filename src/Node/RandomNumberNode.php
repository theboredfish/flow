<?php

namespace Flow\Node;

class RandomNumberNode extends AbstractNode
{
    public function getOutputs(): array
    {
        return ['number' => 'integer'];
    }

    public function execute(): void
    {
        $this->setOutputValue('number', rand(1, 1000));
    }
}
