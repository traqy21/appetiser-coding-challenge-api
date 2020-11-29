<?php


class EventTest extends TestCase
{
    public function testAddEvent() {
        $response = $this->post("/events", []);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure([
            'name',
            'from',
            'to',
            'days'
        ]);

        $testdata = factory(\App\Models\Event::class)->make();
        $response = $this->post("/events", [
            'name' => $testdata->name,
            'from' => $testdata->from,
            'to' => $testdata->to,
            'days' => $testdata->days,
        ]);
        $this->debug($response);
        $response->assertResponseStatus(200);
        $response->seeJsonStructure([
            'message'
        ]);
    }

    public function testEventUpdate(){
        $testCreateData = factory(\App\Models\Event::class)->create();
        $response = $this->post("/events/{$testCreateData->uuid}", []);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure([
            'name',
            'from',
            'to',
            'days'
        ]);

        $testdata = factory(\App\Models\Event::class)->make();
        $response = $this->post("/events/{$testCreateData->uuid}", [
            'name' => $testdata->name,
            'from' => $testdata->from,
            'to' => $testdata->to,
            'days' => json_encode([3,2]),
        ]);
        $this->debug($response);
        $response->assertResponseStatus(200);
        $response->seeJsonStructure([
            'message'
        ]);
    }

    public function testGetEventById(){
        $testdata = factory(\App\Models\Event::class)->create();
        $response = $this->get("/events/{$testdata->uuid}");
        $response->assertResponseStatus(200);
    }
}