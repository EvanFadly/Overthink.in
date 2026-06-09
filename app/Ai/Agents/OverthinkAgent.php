<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class OverthinkAgent implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    protected string $instructions;

    /**
     * Create a new agent instance.
     */
    public function __construct(string $instructions = '')
    {
        $this->instructions = $instructions ?: 'Kamu adalah psikolog absurd yang menganalisis tingkat stress user dengan bahasa Indonesia santai.';
    }

    /**
     * Get the AI provider to use.
     */
    public function provider(): string
    {
        return 'groq';
    }

    /**
     * Get the AI model to use.
     */
    public function model(): string
    {
        return 'llama-3.1-8b-instant';
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return $this->instructions;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'result' => $schema->string()->required(),
            'metadata' => $schema->object([
                'mental_battery' => $schema->string()->required(),
                'delusion_level' => $schema->string()->required(),
                'recommended_action' => $schema->string()->required(),
            ])->required(),
        ];
    }
}
