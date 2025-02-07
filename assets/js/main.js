// main.js - ไฟล์ JavaScript หลักของโปรเจกต์

// ฟังก์ชันสำหรับแสดงข้อความแจ้งเตือน
function showAlert(message, type = 'success') {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type} alert-dismissible fade show`;
    alertContainer.role = 'alert';
    alertContainer.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.prepend(alertContainer);

    // ซ่อนข้อความแจ้งเตือนอัตโนมัติหลัง 3 วินาที
    setTimeout(() => {
        alertContainer.classList.remove('show');
        alertContainer.classList.add('fade');
        alertContainer.addEventListener('transitionend', () => {
            alertContainer.remove();
        });
    }, 3000);
}

// ฟังก์ชันสำหรับยืนยันการลบข้อมูล
function confirmDeletion(event, message = 'คุณต้องการลบข้อมูลนี้หรือไม่?') {
    if (!confirm(message)) {
        event.preventDefault();
    }
}

// ตัวอย่างการเรียกใช้ฟังก์ชัน showAlert
// showAlert('Welcome to the Asset Management System!', 'success');

// Event Listener สำหรับปุ่มลบ (ถ้าจำเป็นต้องใช้งานแบบไดนามิก)
document.addEventListener('DOMContentLoaded', () => {
    const deleteButtons = document.querySelectorAll('.btn-danger');
    deleteButtons.forEach(button => {
        button.addEventListener('click', event => confirmDeletion(event));
    });
});


