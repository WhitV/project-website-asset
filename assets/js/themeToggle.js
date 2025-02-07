document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');

    // ตรวจสอบธีมปัจจุบันจาก localStorage
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'dark') {
        body.classList.add('dark-mode');
        themeIcon.textContent = '☀️'; // แสดงไอคอนพระอาทิตย์
    } else {
        body.classList.remove('dark-mode');
        themeIcon.textContent = '🌙'; // แสดงไอคอนพระจันทร์
    }

    // เพิ่ม Event Listener เพื่อเปลี่ยนธีมเมื่อคลิก
    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        if (body.classList.contains('dark-mode')) {
            themeIcon.textContent = '☀️'; // เปลี่ยนเป็นไอคอนพระอาทิตย์
            localStorage.setItem('theme', 'dark');
        } else {
            themeIcon.textContent = '🌙'; // เปลี่ยนกลับเป็นไอคอนพระจันทร์
            localStorage.setItem('theme', 'light');
        }
    });
});
