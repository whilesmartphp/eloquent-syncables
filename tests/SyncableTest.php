<?php

namespace Whilesmart\UserDevices\Models {
    use Illuminate\Database\Eloquent\Model;

    if (!class_exists(Device::class)) {
        class Device extends Model
        {
            protected $table = 'devices';

            protected $fillable = ['name', 'deviceable_type', 'deviceable_id', 'type', 'token', 'identifier', 'platform'];
        }
    }
}

namespace Whilesmart\Syncables\Tests {
    use Workbench\App\Models\Post;
    use Workbench\App\Models\User;
    use Whilesmart\UserDevices\Models\Device;

    class SyncableTest extends TestCase
    {
        protected function getEnvironmentSetUp($app)
        {
            // Set the configuration that the migration expects if the Device class exists.
            $app['config']->set('user-devices.db_table_name', 'devices');
        }

        public function test_it_automatically_creates_sync_state_on_model_creation(): void
        {
            $post = Post::create([
                'title' => 'Test Post',
            ]);

            $this->assertDatabaseHas('model_sync_states', [
                'syncable_type' => Post::class,
                'syncable_id' => $post->id,
            ]);

            $this->assertNotNull($post->syncState);
            $this->assertNotNull($post->last_synced_at);
        }

        public function test_it_can_mark_model_as_synced(): void
        {
            $post = Post::create([
                'title' => 'Test Post',
            ]);

            $originalSyncTime = $post->syncState->last_synced_at;

            // Wait a second to ensure a different timestamp
            sleep(1);

            $post->markAsSynced();

            $post->refresh();

            $this->assertNotEquals($originalSyncTime, $post->last_synced_at);
        }

        public function test_it_sets_client_generated_id_with_device_token(): void
        {
            $user = User::create([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => bcrypt('password'),
            ]);

            $post = Post::create([
                'title' => 'Test Post',
            ]);

            $deviceToken = 'device-token-123';
            $clientGeneratedId = 'uuid-987-abc';

            $post->setClientGeneratedId($clientGeneratedId, $user, $deviceToken);

            $post->refresh();

            // Check if device was created
            $this->assertDatabaseHas('devices', [
                'deviceable_type' => User::class,
                'deviceable_id' => $user->id,
                'identifier' => $deviceToken,
            ]);

            // Check sync state
            $this->assertDatabaseHas('model_sync_states', [
                'syncable_type' => Post::class,
                'syncable_id' => $post->id,
                'client_generated_id' => $clientGeneratedId,
            ]);

            $this->assertEquals($deviceToken . ':' . $clientGeneratedId, $post->client_generated_id);
        }

        public function test_it_sets_client_generated_id_using_delimited_string(): void
        {
            $user = User::create([
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'password' => bcrypt('password'),
            ]);

            $post = Post::create([
                'title' => 'Test Post',
            ]);

            $deviceToken = 'device-token-456';
            $clientGeneratedId = 'uuid-654-def';
            $delimitedValue = $deviceToken . ':' . $clientGeneratedId;

            $post->setClientGeneratedId($delimitedValue, $user);

            $post->refresh();

            // Check if device was created
            $this->assertDatabaseHas('devices', [
                'deviceable_type' => User::class,
                'deviceable_id' => $user->id,
                'identifier' => $deviceToken,
            ]);

            // Check sync state
            $this->assertDatabaseHas('model_sync_states', [
                'syncable_type' => Post::class,
                'syncable_id' => $post->id,
                'client_generated_id' => $clientGeneratedId,
            ]);

            $this->assertEquals($delimitedValue, $post->client_generated_id);
        }

        public function test_it_updates_device_user_id_if_changed(): void
        {
            $user1 = User::create([
                'name' => 'User 1',
                'email' => 'user1@example.com',
                'password' => bcrypt('password'),
            ]);

            $user2 = User::create([
                'name' => 'User 2',
                'email' => 'user2@example.com',
                'password' => bcrypt('password'),
            ]);

            $post = Post::create([
                'title' => 'Test Post',
            ]);

            $deviceToken = 'device-token-shared';
            $clientGeneratedId = 'uuid-abc';

            // First set with user1
            $post->setClientGeneratedId($clientGeneratedId, $user1, $deviceToken);

            $this->assertDatabaseHas('devices', [
                'deviceable_type' => User::class,
                'deviceable_id' => $user1->id,
                'identifier' => $deviceToken,
            ]);

            // Set again with user2
            $post->setClientGeneratedId($clientGeneratedId, $user2, $deviceToken);

            // Device owner should have updated to user2
            $this->assertDatabaseHas('devices', [
                'deviceable_type' => User::class,
                'deviceable_id' => $user2->id,
                'identifier' => $deviceToken,
            ]);
        }
    }
}
