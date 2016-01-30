<?php
//use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
use Minhbang\User\User;

class ImageApiTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * @var array
     */
    protected $users = [];

    public function setUp()
    {
        parent::setUp();
        $this->users['user'] = factory(User::class)->create();
    }


    /**
     * Lấy danh sách hình ảnh
     */
    public function testGetImageData()
    {
        // Yêu cầu đăng nhập khi truy cập
        $this->visit('/image/data')
            ->seePageIs('/auth/login');

        // Truy cập thành công
        $this->actingAs($this->users['user'])->get('/image/data')
            ->assertResponseOk();
    }
}