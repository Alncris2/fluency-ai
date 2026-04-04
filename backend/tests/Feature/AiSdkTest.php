<?php

namespace Tests\Feature;

use App\Ai\Agents\FluencyAgent;
use Laravel\Ai\Gateway\FakeTextGateway;
use Tests\TestCase;

class AiSdkTest extends TestCase
{
    /**
     * Verifica que o SDK está configurado corretamente e o FluencyAgent
     * consegue processar um prompt usando a integração fake do Laravel AI.
     */
    public function test_fluency_agent_returns_faked_response(): void
    {
        FluencyAgent::fake(['Olá! Estou pronto para ajudar.']);

        $response = FluencyAgent::make()->prompt('Oi, tudo bem?');

        $this->assertSame('Olá! Estou pronto para ajudar.', $response->text);
    }

    /**
     * Verifica que o agente usa as instruções definidas na classe.
     */
    public function test_fluency_agent_has_instructions(): void
    {
        $agent = FluencyAgent::make();

        $this->assertNotEmpty($agent->instructions());
    }

    /**
     * Verifica que o FakeTextGateway é retornado ao chamar fake(),
     * confirmando que a integração com o SDK está funcional.
     */
    public function test_fluency_agent_fake_returns_fake_gateway(): void
    {
        $fake = FluencyAgent::fake();

        $this->assertInstanceOf(FakeTextGateway::class, $fake);
    }
}
