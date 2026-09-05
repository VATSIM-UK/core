<?php

namespace Tests\Unit\Account;

use App\Models\Mship\Note\Type;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SecurityNoteTypeTest extends TestCase
{
    #[Test]
    public function it_has_a_security_note_type(): void
    {
        $type = Type::isShortCode('security')->first();

        $this->assertNotNull($type);
        $this->assertEquals('Security', $type->name);
        $this->assertTrue((bool) $type->is_available);
        $this->assertFalse((bool) $type->is_system);
    }
}
