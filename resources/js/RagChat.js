import React, { useState, useEffect } from "react";
import axios from "axios";

const RagChat = () => {
    const [question, setQuestion] = useState("");
    const [answer, setAnswer] = useState("");
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState("");
    const [messages, setMessages] = useState([
        { sender: "bot", text: "مرحبا! كيف يمكنني مساعدتك اليوم؟" },
    ]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError("");
        setAnswer("");
        setMessages((prev) => [...prev, { sender: "user", text: question }]);
        try {
            const res = await axios.get("http://localhost:8000/api/rag/ask", {
                params: { q: question },
            });
            setAnswer(res.data.answer);
            setMessages((prev) => [
                ...prev,
                { sender: "bot", text: res.data.answer },
            ]);
        } catch (err) {
            setError("Something went wrong.");
        } finally {
            setLoading(false);
            setQuestion("");
        }
    };

    return (
        <div
            style={{
                maxWidth: 600,
                margin: "40px auto",
                padding: 24,
                background: "#fff",
                borderRadius: 8,
                boxShadow: "0 2px 8px #eee",
            }}
        >
            <h2 style={{ textAlign: "center" }}>RAG Chatbot</h2>
            <div style={{ minHeight: 120, marginBottom: 16 }}>
                {messages.map((msg, idx) => (
                    <div
                        key={idx}
                        style={{
                            textAlign: msg.sender === "bot" ? "left" : "right",
                            margin: "8px 0",
                        }}
                    >
                        <span
                            style={{
                                background:
                                    msg.sender === "bot"
                                        ? "#f6f8fa"
                                        : "#d1e7dd",
                                padding: 8,
                                borderRadius: 4,
                                display: "inline-block",
                            }}
                        >
                            {msg.text}
                        </span>
                    </div>
                ))}
            </div>
            <form
                onSubmit={handleSubmit}
                style={{ display: "flex", gap: 8, marginBottom: 16 }}
            >
                <input
                    type="text"
                    value={question}
                    onChange={(e) => setQuestion(e.target.value)}
                    placeholder="اكتب سؤالك..."
                    style={{
                        flex: 1,
                        padding: 8,
                        borderRadius: 4,
                        border: "1px solid #ccc",
                    }}
                    required
                />
                <button
                    type="submit"
                    style={{
                        padding: "8px 16px",
                        borderRadius: 4,
                        background: "#007bff",
                        color: "#fff",
                        border: "none",
                    }}
                    disabled={loading}
                >
                    {loading ? "جاري..." : "إرسال"}
                </button>
            </form>
            {error && (
                <div style={{ color: "red", marginBottom: 8 }}>{error}</div>
            )}
        </div>
    );
};

export default RagChat;
