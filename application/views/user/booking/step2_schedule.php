<?php
/**
 * Step 2: Select Schedule & Time Slot View
 * UC-USR-08: Kalender & Slot Waktu
 */
?>

<div class="form-section">
    <h4>Pilih Jadwal & Slot Waktu</h4>
    <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
        <span style="display: inline-block; width: 12px; height: 12px; background: #d1fae5; border-radius: 3px; margin-right: 5px;"></span> Tersedia
        <span style="display: inline-block; width: 12px; height: 12px; background: #e5e7eb; border-radius: 3px; margin-left: 10px; margin-right: 5px;"></span> Penuh
        <span style="display: inline-block; width: 12px; height: 12px; background: #fee2e2; border-radius: 3px; margin-left: 10px; margin-right: 5px;"></span> Libur/Tutup
    </p>
    
    <?= form_open('booking/step2/' . $workshop['id'], ['id' => 'slotForm']) ?>
    
    <!-- Calendar -->
    <div class="calendar-container">
        <div class="calendar-header">
            <button type="button" class="calendar-nav" id="prevMonth">← Prev</button>
            <h4 id="currentMonthYear"><?= date('F Y', mktime(0, 0, 0, (int)$current_month, 1, (int)$current_year)) ?></h4>
            <button type="button" class="calendar-nav" id="nextMonth">Next →</button>
        </div>
        
        <div class="calendar-grid">
            <div class="calendar-day-header">Min</div>
            <div class="calendar-day-header">Sen</div>
            <div class="calendar-day-header">Sel</div>
            <div class="calendar-day-header">Rab</div>
            <div class="calendar-day-header">Kam</div>
            <div class="calendar-day-header">Jum</div>
            <div class="calendar-day-header">Sab</div>
            
            <?php
            // Empty cells for days before first day of month
            $first_day = date('w', mktime(0, 0, 0, (int)$current_month, 1, (int)$current_year));
            for ($i = 0; $i < $first_day; $i++): ?>
                <div></div>
            <?php endfor; ?>
            
            <?php foreach ($calendar_data as $day => $data): ?>
                <div class="calendar-day 
                    <?= $data['is_available'] ? 'available' : '' ?> 
                    <?= $data['is_blocked'] ? 'holiday' : '' ?>
                    <?= $data['is_closed'] ? 'full' : '' ?>
                    <?= $data['date'] == $selected_date ? 'selected' : '' ?>
                    <?= $data['slots_count'] > 0 ? 'has-slots' : '' ?>"
                    data-date="<?= $data['date'] ?>"
                    data-available="<?= $data['is_available'] ? '1' : '0' ?>">
                    <?= $day ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Hidden field for selected slot -->
    <input type="hidden" name="slot_date" id="slot_date" value="<?= $selected_date ?>">
    <input type="hidden" name="slot_time" id="slot_time" value="">
    
    <!-- Time Slots -->
    <div id="slotsSection" style="margin-top: 20px; display: <?= $selected_date ? 'block' : 'none' ?>;">
        <h5 style="margin-bottom: 10px;">Slot Waktu untuk <span id="selectedDateText"><?= $selected_date ? date('d M Y', strtotime($selected_date)) : '' ?></span></h5>
        
        <div class="slots-container" id="slotsContainer">
            <?php if (!empty($available_slots)): ?>
                <?php foreach ($available_slots as $slot): ?>
                    <div class="slot-btn" 
                         data-time="<?= $slot['slot_time'] ?>" 
                         data-remaining="<?= $slot['remaining_capacity'] ?>">
                        <div><?= date('H:i', strtotime($slot['slot_time'])) ?></div>
                        <div class="slot-remaining"><?= $slot['remaining_capacity'] ?>/<?= $slot['slot_capacity'] ?> tersisa</div>
                    </div>
                <?php endforeach; ?>
            <?php elseif ($selected_date): ?>
                <p style="color: #999; font-size: 13px;">Tidak ada slot tersedia untuk tanggal ini.</p>
            <?php else: ?>
                <p style="color: #999; font-size: 13px;">Pilih tanggal untuk melihat slot waktu.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="btn-group">
        <a href="<?= site_url('booking/step1/' . $workshop['id']) ?>" class="btn btn-secondary">← Kembali</a>
        <button type="submit" class="btn btn-primary btn-block" id="submitBtn" disabled>Lanjut ke Layanan →</button>
    </div>
    
    <?= form_close() ?>
</div>

<script>
const workshopId = <?= $workshop['id'] ?>;
const currentYear = <?= $current_year ?>;
const currentMonth = <?= (int)$current_month ?>;

document.addEventListener('DOMContentLoaded', function() {
    const calendarDays = document.querySelectorAll('.calendar-day[data-date]');
    const slotsContainer = document.getElementById('slotsContainer');
    const slotsSection = document.getElementById('slotsSection');
    const slotDateInput = document.getElementById('slot_date');
    const slotTimeInput = document.getElementById('slot_time');
    const submitBtn = document.getElementById('submitBtn');
    const selectedDateText = document.getElementById('selectedDateText');
    const currentMonthYear = document.getElementById('currentMonthYear');
    
    let selectedDate = null;
    let selectedTime = null;
    
    // Calendar day click handler
    calendarDays.forEach(day => {
        day.addEventListener('click', function() {
            const date = this.dataset.date;
            const available = this.dataset.available;
            
            if (available !== '1') {
                alert('Tanggal ini tidak tersedia untuk booking.');
                return;
            }
            
            // Remove previous selection
            calendarDays.forEach(d => d.classList.remove('selected'));
            this.classList.add('selected');
            
            selectedDate = date;
            slotDateInput.value = date;
            selectedDateText.textContent = formatDate(date);
            slotsSection.style.display = 'block';
            
            // Load slots via AJAX
            loadSlots(date);
        });
    });
    
    // Slot button click handler
    slotsContainer.addEventListener('click', function(e) {
        const slotBtn = e.target.closest('.slot-btn');
        if (!slotBtn) return;
        
        // Remove previous selection
        document.querySelectorAll('.slot-btn').forEach(btn => btn.classList.remove('selected'));
        slotBtn.classList.add('selected');
        
        selectedTime = slotBtn.dataset.time;
        slotTimeInput.value = selectedTime;
        submitBtn.disabled = false;
    });
    
    // Month navigation
    document.getElementById('prevMonth').addEventListener('click', function() {
        navigateMonth(-1);
    });
    
    document.getElementById('nextMonth').addEventListener('click', function() {
        navigateMonth(1);
    });
    
    function navigateMonth(delta) {
        let newMonth = currentMonth + delta;
        let newYear = currentYear;
        
        if (newMonth < 1) {
            newMonth = 12;
            newYear--;
        } else if (newMonth > 12) {
            newMonth = 1;
            newYear++;
        }
        
        window.location.href = '<?= site_url('booking/step2/' . $workshop['id']) ?>' + 
            '?month=' + String(newMonth).padStart(2, '0') + '&year=' + newYear +
            (selectedDate ? '&date=' + selectedDate : '');
    }
    
    function loadSlots(date) {
        slotsContainer.innerHTML = '<div style="text-align: center; padding: 20px;"><span class="spinner"></span> Memuat slot...</div>';
        
        fetch(`<?= site_url('booking/ajax_get_slots/') ?>${workshopId}?date=${date}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.slots.length > 0) {
                    let html = '';
                    data.data.slots.forEach(slot => {
                        html += `
                            <div class="slot-btn" data-time="${slot.time_full}" data-remaining="${slot.remaining}">
                                <div>${slot.time}</div>
                                <div class="slot-remaining">${slot.remaining}/${slot.capacity} tersisa</div>
                            </div>
                        `;
                    });
                    slotsContainer.innerHTML = html;
                } else if (data.data.is_closed) {
                    slotsContainer.innerHTML = '<p style="color: #999; text-align: center;">Bengkel tutup pada hari ini.</p>';
                } else if (data.data.is_blocked) {
                    slotsContainer.innerHTML = '<p style="color: #dc2626; text-align: center;">Tanggal ini adalah hari libur.</p>';
                } else {
                    slotsContainer.innerHTML = '<p style="color: #999; text-align: center;">Tidak ada slot tersedia.</p>';
                }
                
                submitBtn.disabled = true;
                slotTimeInput.value = '';
            })
            .catch(error => {
                console.error('Error loading slots:', error);
                slotsContainer.innerHTML = '<p style="color: #dc2626; text-align: center;">Gagal memuat slot. Silakan coba lagi.</p>';
            });
    }
    
    function formatDate(dateStr) {
        const date = new Date(dateStr);
        const options = { day: 'numeric', month: 'short', year: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    }
});
</script>
