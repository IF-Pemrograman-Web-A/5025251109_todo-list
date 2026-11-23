<main>
<?php if ($menu === 'personal'): ?>
    <section>
        <ul id="personalList"></ul>
    </section>
    <aside>
        <h2>Add Task</h2>
        <form id="personalForm">
            <p>Date</p>
            <input type="date" required>
            <p>Task Name</p>
            <input type="text" required>
            <p>Time</p>
            <input type="time" required>
            <p>Notification Time</p>
            <input type="time" required>
            <p>Take Photo</p>
            <video id="kamera" width="100%" autoplay></video>
            <button type="button" id="btnSnap" style="margin-bottom: 10px;">Capture</button>
            <canvas id="kanvas" style="display: none;"></canvas>
            <img id="hasilFoto" style="display: none; width: 100%; margin-bottom: 10px;">
            <button type="submit">Save</button>
        </form>
        <button type="button" id="btnTema">Theme Change</button>
    </aside>
<?php else: ?>
    <section>
        <div id="apiStatus" class="api-status" style="display: none;"></div>
        <ul id="sharedList"></ul>
    </section>
    <aside>
        <h2 id="sharedFormTitle">Add Task</h2>
        <form id="sharedForm">
            <input type="hidden" id="editId">
            <p>Date</p>
            <input type="date" id="sharedTgl" required>
            <p>Task Name</p>
            <input type="text" id="sharedNama" required>
            <p>Time</p>
            <input type="time" id="sharedJam" required>
            <p>Notification Time</p>
            <input type="time" id="sharedJamNotif" required>
            <p>Upload Photo</p>
            <input type="file" id="sharedFile" accept="image/*">
            <p>Take Photo</p>
            <video id="kamera" width="100%" autoplay></video>
            <button type="button" id="btnSnap" style="margin-bottom: 10px;">Capture</button>
            <canvas id="kanvas" style="display: none;"></canvas>
            <img id="hasilFoto" style="display: none; width: 100%; margin-bottom: 10px;">
            <button type="submit" id="btnSharedSubmit">Save</button>
            <button type="button" id="btnCancelEdit" style="display: none;">Cancel</button>
        </form>
        <button type="button" id="btnTema">Theme Change</button>
    </aside>
<?php endif; ?>
</main>
