<?php

namespace BRKsDeadPool\Friendship\Interfaces;

interface MigrationContract {
    public function up();

    public function down();
}