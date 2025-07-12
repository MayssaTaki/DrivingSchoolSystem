// server.js (في Node.js)
const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const app = express();
app.use(cors());
app.use(express.json());

const server = http.createServer(app);
const io = new Server(server, { cors: { origin: '*', methods: ['GET', 'POST'] } });

// استقبال الرسالة من Laravel
app.post('/broadcast', (req, res) => {
    const { room, message } = req.body;
    io.to(room).emit('receive_message', message);
    console.log(`📢 Broadcasted to ${room}:`, message);
    res.send({ success: true });
});

io.on('connection', (socket) => {
    console.log('🔌 User connected:', socket.id);

    socket.on('join_room', (room) => {
        socket.join(room);
        console.log(`${socket.id} joined room: ${room}`);
    });

    socket.on('disconnect', () => {
        console.log('❌ User disconnected:', socket.id);
    });
});

server.listen(3000, () => {
    console.log('🚀 Socket.IO server running at http://localhost:3000');
});
