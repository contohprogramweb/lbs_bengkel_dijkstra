<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_schedule_tables extends CI_Migration {

    public function up()
    {
        // 1. Tabel Konfigurasi Jadwal Harian (Senin-Minggu)
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'workshop_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE],
            'day_of_week' => ['type' => 'TINYINT', 'constraint' => 1, 'comment' => '0=Sun, 1=Mon, ... 6=Sat'],
            'is_open' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'open_time' => ['type' => 'TIME', 'null' => TRUE],
            'close_time' => ['type' => 'TIME', 'null' => TRUE],
            'slot_interval' => ['type' => 'INT', 'constraint' => 11, 'default' => 60, 'comment' => 'menit: 30, 60, 120'],
            'capacity_per_slot' => ['type' => 'INT', 'constraint' => 11, 'default' => 1, 'comment' => '1-20 kendaraan'],
            'created_at' => ['type' => 'DATETIME', 'null' => TRUE],
            'updated_at' => ['type' => 'DATETIME', 'null' => TRUE]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key(['workshop_id', 'day_of_week']);
        $this->dbforge->create_table('workshop_schedules');

        // 2. Tabel Hari Libur / Blokir Tanggal Spesifik
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'workshop_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE],
            'blocked_date' => ['type' => 'DATE'],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
            'is_full_day' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => TRUE]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key(['workshop_id', 'blocked_date']);
        $this->dbforge->create_table('workshop_blocked_dates');

        // 3. Tabel Slot Terblokir Spesifik (Jam tertentu dalam hari aktif)
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'workshop_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE],
            'slot_date' => ['type' => 'DATE'],
            'slot_time' => ['type' => 'TIME'],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
            'created_at' => ['type' => 'DATETIME', 'null' => TRUE]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key(['workshop_id', 'slot_date', 'slot_time']);
        $this->dbforge->create_table('workshop_blocked_slots');
    }

    public function down()
    {
        $this->dbforge->drop_table('workshop_blocked_slots');
        $this->dbforge->drop_table('workshop_blocked_dates');
        $this->dbforge->drop_table('workshop_schedules');
    }
}
