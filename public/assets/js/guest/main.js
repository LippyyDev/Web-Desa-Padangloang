document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.navbar .nav-link');
    navLinks.forEach((link) => {
        if (link.href === window.location.href) {
            link.classList.add('active');
        }
    });

    // ===== HERO INTERACTIVE FIREFLY SWARM =====
    const canvas = document.getElementById('heroInteractiveCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let width, height;
        let fireflies = [];
        
        // Configuration
        const fireflyCount = 80;
        const mouseRadius = 250;
        const colorPalette = [
            '16, 185, 129', // Emerald
            '13, 110, 253', // Blue
            '6, 182, 212'   // Cyan
        ];
        
        let mouse = { x: -1000, y: -1000 };

        // Ripple Configuration
        let ripples = [];
        
        // Resize handler
        function resize() {
            width = canvas.width = canvas.parentElement.offsetWidth;
            height = canvas.height = canvas.parentElement.offsetHeight;
            initFireflies();
        }
        
        class Firefly {
            constructor() {
                this.init();
            }
            
            init() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.vx = (Math.random() - 0.5) * 0.5; // Slower wander velocity
                this.vy = (Math.random() - 0.5) * 0.5;
                this.size = Math.random() * 2 + 0.5;
                this.life = Math.random() * 100;
                this.rgb = colorPalette[Math.floor(Math.random() * colorPalette.length)];
                this.maxSpeed = 0.8; // Reduced max speed
            }
            
            update() {
                // Mouse Attraction
                const dx = mouse.x - this.x;
                const dy = mouse.y - this.y;
                const dist = Math.sqrt(dx*dx + dy*dy);
                
                if (dist < mouseRadius) {
                    // Accel towards mouse
                    const force = (mouseRadius - dist) / mouseRadius; // 0 to 1
                    this.vx += (dx / dist) * force * 0.05; // Reduced attraction force
                    this.vy += (dy / dist) * force * 0.05;
                }

                // Ripple Repulsion
                ripples.forEach(ripple => {
                    const rdx = ripple.x - this.x;
                    const rdy = ripple.y - this.y;
                    const rDist = Math.sqrt(rdx*rdx + rdy*rdy);
                    
                    if (rDist < ripple.radius + 50 && rDist > ripple.radius - 50) {
                        const force = (100 - Math.abs(rDist - ripple.radius)) / 100;
                        const angle = Math.atan2(rdy, rdx);
                        this.vx -= Math.cos(angle) * force * 2; // Strong push
                        this.vy -= Math.sin(angle) * force * 2;
                    }
                });
                
                // Add some randomness (jitter)
                this.vx += (Math.random() - 0.5) * 0.05;
                this.vy += (Math.random() - 0.5) * 0.05;
                
                // Speed Limit
                const speed = Math.sqrt(this.vx*this.vx + this.vy*this.vy);
                const currentMax = this.maxSpeed + (ripples.length > 0 ? 5 : 0); // Allow higher speed during burst
                if (speed > currentMax) {
                    this.vx = (this.vx / speed) * currentMax;
                    this.vy = (this.vy / speed) * currentMax;
                }
                
                // Update position
                this.x += this.vx;
                this.y += this.vy;
                
                // Wrap around screen
                if (this.x < -10) this.x = width + 10;
                if (this.x > width + 10) this.x = -10;
                if (this.y < -10) this.y = height + 10;
                if (this.y > height + 10) this.y = -10;
                
                // Pulse Animation
                this.life++;
            }
            
            draw() {
                const opacity = (Math.sin(this.life * 0.05) + 1) / 2 * 0.5; // Reduced max opacity
                
                // Draw Glow
                const gradient = ctx.createRadialGradient(this.x, this.y, 0, this.x, this.y, this.size * 4);
                gradient.addColorStop(0, `rgba(${this.rgb}, ${opacity})`);
                gradient.addColorStop(1, `rgba(${this.rgb}, 0)`);
                
                ctx.fillStyle = gradient;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size * 4, 0, Math.PI * 2);
                ctx.fill();
                
                // Draw Core
                ctx.fillStyle = `rgba(255, 255, 255, ${opacity + 0.2})`;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }
        
        function initFireflies() {
            fireflies = [];
            for (let i = 0; i < fireflyCount; i++) {
                fireflies.push(new Firefly());
            }
        }
        
        function animate() {
            ctx.clearRect(0, 0, width, height);
            
            // Draw Ripples
            ripples.forEach((ripple, index) => {
                ripple.radius += 5;
                ripple.alpha -= 0.02;
                
                if (ripple.alpha <= 0) {
                    ripples.splice(index, 1);
                } else {
                    ctx.beginPath();
                    ctx.arc(ripple.x, ripple.y, ripple.radius, 0, Math.PI * 2);
                    ctx.strokeStyle = `rgba(16, 185, 129, ${ripple.alpha})`;
                    ctx.lineWidth = 2;
                    ctx.stroke();
                }
            });

            fireflies.forEach(f => {
                f.update();
                f.draw();
            });
            
            requestAnimationFrame(animate);
        }
        
        window.addEventListener('resize', () => {
             clearTimeout(window.resizeTimer);
             window.resizeTimer = setTimeout(resize, 200);
        });
        
        const heroSection = document.getElementById('heroSection');
        if (heroSection) {
            heroSection.addEventListener('mousemove', (e) => {
                const rect = heroSection.getBoundingClientRect();
                mouse.x = e.clientX - rect.left;
                mouse.y = e.clientY - rect.top;
            });
            
            heroSection.addEventListener('mouseleave', () => {
                mouse.x = -1000;
                mouse.y = -1000;
            });

            // Click Effect
            heroSection.addEventListener('click', (e) => {
                const rect = heroSection.getBoundingClientRect();
                ripples.push({
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top,
                    radius: 0,
                    alpha: 1
                });
            });
        }
        
        resize();
        animate();
    }

    // ===== INTERACTIVE SPOTLIGHT SECTIONS =====
    const interactiveSections = document.querySelectorAll('.about-section, .news-section');
    interactiveSections.forEach(section => {
        section.addEventListener('mousemove', (e) => {
            const rect = section.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            section.style.setProperty('--mouse-x', `${x}px`);
            section.style.setProperty('--mouse-y', `${y}px`);
        });
    });
});

